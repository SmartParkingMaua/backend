'use strict';

const ONE_SECOND_TIMESTAMP_MILLISECONDS = 1000;
const ONE_MINUTE_TIMESTAMP_MILLISECONDS = 60000;
const ONE_HOUR_TIMESTAMP_MILLISECONDS = 3600000;
const ONE_DAY_TIMESTAMP_MILLISECONDS = 86400000;
const ONE_WEEK_TIMESTAMP_MILLISECONDS = 604800000;
const ONE_YEAR_TIMESTAMP_MILLISECONDS = 31536000000;

const nameOfDayOfWeek = [ 'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sabado' ];

const nameOfMonth = [ 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
        'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro' ];

const mysql = require('mysql');
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const app = express();

const port = 8080;

const pool = mysql.createPool({
    connectionLimit: 10,
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'mydb',
    debug: false
});


app.use(cors());
app.use(bodyParser.json());


// -----Help functions----- //
function monthDays( timestamp ) {

    let date = new Date( timestamp );
    let month = date.getMonth() + 1;
    let year = date.getFullYear();

    return new Date( year, month, 0 ).getDate();

}

function extractHourTimestamp( timestamp ) {

    let date = new Date( timestamp );
    let minutesTimestamp = date.getMinutes() * ONE_MINUTE_TIMESTAMP_MILLISECONDS;
    let secondsTimestamp = date.getSeconds() * ONE_SECOND_TIMESTAMP_MILLISECONDS;
    let millisecondsTimestamp = date.getMilliseconds();

    return timestamp - minutesTimestamp - secondsTimestamp - millisecondsTimestamp;

}

function extractDayTimestamp( timestamp ) {

    let currentHourTimestamp = extractHourTimestamp( timestamp );
    let date = new Date( currentHourTimestamp );
    let hoursTimestamp = date.getHours() * ONE_HOUR_TIMESTAMP_MILLISECONDS;
    let timestampTo = currentHourTimestamp - hoursTimestamp;

    return adjustTimezoneTimestamp( currentHourTimestamp, timestampTo );

}

function extractMonthTimestamp( timestamp ) {

    let date = new Date( timestamp );
    let daysTimestamp = ( date.getDate() - 1 ) * ONE_DAY_TIMESTAMP_MILLISECONDS;
    let firstDayOfMonthTimestamp = timestamp - daysTimestamp;
    let adjustedFirstDayOfMonthTimestamp =
            adjustTimezoneTimestamp( timestamp, firstDayOfMonthTimestamp );

    return extractDayTimestamp( adjustedFirstDayOfMonthTimestamp );

}

function adjustTimezoneTimestamp( timestampFrom, timestampTo ) {

    let timezoneFrom = new Date( timestampFrom ).getTimezoneOffset();
    let timezoneTo = new Date( timestampTo ).getTimezoneOffset()
    let diffHoursBetweenTimezones = ( timezoneTo - timezoneFrom ) / 60;

    return timestampTo + diffHoursBetweenTimezones * ONE_HOUR_TIMESTAMP_MILLISECONDS;

}

/**
 * Usually, most of the timestampFrom will have its time set to 00:00:00 and
 * timestampTo to 23:59:59. But it turns when daylight saving time begins,
 * there's no time as 00:00:00. Instead, the time is set to 01:00:00. So it's
 * necessary to subtract one hour period timestamp from timestampTo
 * (which is 00:59:59, in these cases) to adjust the whole period timestamp
 */
function adjustStartOfDaylightSaving( timestampFrom, timestampTo ) {

    let hourFrom = new Date( timestampFrom ).getHours();
    let hourTo = new Date( timestampTo ).getHours();

    if ( hourFrom === 1 && hourTo === 0 ) {
        timestampTo -= ONE_HOUR_TIMESTAMP_MILLISECONDS;
    }

    return timestampTo;

}

function isLeapYear( timestamp ) {

    let year = new Date( timestamp ).getFullYear();

    return ( ( year % 4 == 0 ) && ( year % 100 != 0 ) ) || ( year % 400 == 0 );

}

function isMonthUntilFebruary( timestamp ) {

    let month = new Date( timestamp ).getMonth();

    return month <= 1;

}


// -----Routes----- //
app.get( '/', ( req, res ) => {

    res.send( 'Welcome to SmartParking Mauá API' );

});

app.post( '/v1/cars', ( req, res ) => {

    let action = req.body.estado;
    let parkingId = Number( req.params.id );
    let timestampInMilliseconds = Number( req.query.timestamp );
    let timestampInSeconds = timestampInMilliseconds / 1000;

    let query;

    if ( parkingId <= 2 ) {
        query = "INSERT INTO tbl_portaria (idportaria, timestamp, acao) VALUES"
                + " (?, FROM_UNIXTIME(?), ?);";
    }
    else {
        query = "INSERT INTO tbl_bolsao (idbolsao, timestamp, acao) VALUES"
                + " (?, FROM_UNIXTIME(?), ?);";
    }

    pool.query( query, [ parkingId, timestampInSeconds, action ], ( error, results, fields ) => {
        if (error) {
            res.status(400);
            res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
        }
        else {
            res.status(201);
            res.end();
        }
    });

});

app.get( '/v1/parkings', ( req, res ) => {

    pool.query( 'SELECT * from tbl_atual;', ( error, results, fields ) => {
        if ( error ) {
            res.status(400);
            res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
        }
        else {
            let parkings = [];

            results.forEach( element => {
                parkings.push({
                    id: element.idbp,
                    name: element.nome,
                    lots: {
                        occupied: element.vagas_ocupadas,
                        max: element.max_vagas
                    } 
                });
            });

            let returnPackage = { parkings: parkings };

            res.status(200);
            res.send( JSON.stringify( returnPackage ) );
        }
    });

});

app.get( '/v1/parkings/:id/findByHour', ( req, res ) => {

    let parkingId = Number( req.params.id );
    let timestampInMilliseconds = extractHourTimestamp( Number( req.query.timestamp ) );
    let timestampInSeconds = timestampInMilliseconds / 1000;

    let query = "SELECT acao AS action";

    for ( let i = 0; i < 12; i++ ) {
        query += ", COUNT(CASE WHEN MINUTE(timestamp) BETWEEN " + ( 5 * i ) + " AND "
                + ( 5 * i + 4 ) + " THEN acao END) '" + ( 5 * ( i + 1 ) ) + "m'";
    }

    if ( parkingId === 0 ) { // Campus
        query += " FROM tbl_portaria WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(?)) AND"
                + " HOUR(timestamp) = HOUR(FROM_UNIXTIME(?)) GROUP BY acao;";
    }
    else if ( parkingId <= 2 ) { // Portarias
        query += " FROM tbl_portaria WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(?)) AND"
                + " HOUR(timestamp) = HOUR(FROM_UNIXTIME(?)) AND idportaria=? GROUP BY acao;";
    }
    else { // Bolsões
        query += " FROM tbl_bolsao WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(?)) AND"
                + " HOUR(timestamp) = HOUR(FROM_UNIXTIME(?)) AND idbolsao=? GROUP BY acao;";
    }

    pool.query( query, [ timestampInSeconds, timestampInSeconds, parkingId ],
        ( error, results, fields ) => {
            if ( error ) {
                res.status(400);
                res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
            }
            else {
                let entranceList = Array.apply( null, Array(12) ).map( ( e, i ) =>
                        e = { period: ( 5 * ( i + 1 ) ) + 'm', value: 0 } );
                let exitList = Array.apply( null, Array(12) ).map( ( e, i ) =>
                        e = { period: ( 5 * ( i + 1 ) ) + 'm', value: 0 } );

                results.forEach( element => {
                    if ( element.action === 'entrada' ) {
                        for ( let i = 0; i < entranceList.length; i++ ) {
                            entranceList[i].value = element[ entranceList[i].period ];
                        }
                    }
                    else if ( element.action === 'saida' ) {
                        for ( let i = 0; i < exitList.length; i++ ) {
                            exitList[i].value = element[ exitList[i].period ];
                        }
                    }
                });

                let returnPackage = {
                    entrance: entranceList,
                    exit: exitList,
                    timestampFrom: timestampInMilliseconds
                };

                res.status(200);
                res.send( JSON.stringify( returnPackage ) );
            }
        });

});

app.get( '/v1/parkings/:id/findByDay', ( req, res ) => {

    let parkingId = Number( req.params.id );
    let timestampInMilliseconds = extractDayTimestamp( Number( req.query.timestamp ) );
    let timestampInSeconds = timestampInMilliseconds / 1000;

    let query = "SELECT acao AS action";

    for ( let i = 0; i < 24; i++ ) {
        query += ", COUNT(CASE WHEN HOUR(timestamp) = " + i + " THEN acao END) '"
                + ( i + 1 ) + "h'";
    }

    if ( parkingId === 0 ) { // Campus
        query += " FROM tbl_portaria WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(?))"
                + " GROUP BY acao;";
    }
    else if ( parkingId <= 2 ) { // Portarias
        query += " FROM tbl_portaria WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(?))"
                + " AND idportaria=? GROUP BY acao;";
    }
    else { // Bolsões
        query += " FROM tbl_bolsao WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(?))"
                + " AND idbolsao=? GROUP BY acao;";
    }

    pool.query( query, [ timestampInSeconds, parkingId ], ( error, results, fields ) => {
        if ( error ) {
            res.status(400);
            res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
        }
        else {
            let entranceList = Array.apply( null, Array(24) ).map( ( e, i ) =>
                    e = { period: ( i + 1 ) + 'h', value: 0 } );
            let exitList = Array.apply( null, Array(24) ).map( ( e, i ) =>
                    e = { period: ( i + 1 ) + 'h', value: 0 } );

            results.forEach( element => {
                if ( element.action === 'entrada' ) {
                    for ( let i = 0; i < entranceList.length; i++ ) {
                        entranceList[i].value = element[ entranceList[i].period ];
                    }
                }
                else if ( element.action === 'saida' ) {
                    for (let i = 0; i < exitList.length; i++) {
                        exitList[i].value = element[ exitList[i].period ];
                    }
                }
            });

            let returnPackage = {
                entrance: entranceList,
                exit: exitList,
                timestampFrom: timestampInMilliseconds
            };

            res.status(200);
            res.send( JSON.stringify( returnPackage ) );
        }
    });

});

app.get( '/v1/parkings/:id/findByWeek', ( req, res ) => {

    let parkingId = Number( req.params.id );
    let timestampInMilliseconds = Number( req.query.timestamp );
    let timestampFromInMilliseconds = extractDayTimestamp( timestampInMilliseconds );
    let timestampFromInSeconds = timestampFromInMilliseconds / 1000;
    let weekPeriodTimestampInMilliseconds = ONE_WEEK_TIMESTAMP_MILLISECONDS - 1;

    let timestampToInMilliseconds = adjustTimezoneTimestamp( timestampFromInMilliseconds,
            timestampFromInMilliseconds + weekPeriodTimestampInMilliseconds );

    timestampToInMilliseconds =
            adjustStartOfDaylightSaving( timestampInMilliseconds, timestampToInMilliseconds );

    let timestampToInSeconds = timestampToInMilliseconds / 1000;

    let query = "SELECT acao AS action";

    for ( let i = 0; i < 7; i++ ) {
        query += ", COUNT(CASE WHEN DAYOFWEEK(timestamp) = " + ( i + 1 ) + " THEN acao END) '"
                + nameOfDayOfWeek[i] + "'";
    }

    if ( parkingId === 0 ) { // Campus
        query += " FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) GROUP BY acao;";
    }
    else if ( parkingId <= 2 ) { // Portarias
        query += " FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) AND idportaria=? GROUP BY acao;";
    }
    else { // Bolsões
        query += " FROM tbl_bolsao WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) AND idbolsao=? GROUP BY acao;";
    }

    pool.query( query, [ timestampFromInSeconds, timestampToInSeconds, parkingId ],
        ( error, results, fields ) => {
            if ( error ) {
                res.status(400);
                res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
            }
            else {
                let entranceList = Array.apply( null, Array(7) ).map( ( e, i ) =>
                        e = { period: nameOfDayOfWeek[i], value: 0 } );
                let exitList = Array.apply( null, Array(7) ).map( ( e, i ) =>
                        e = { period: nameOfDayOfWeek[i], value: 0 } );

                results.forEach( element => {
                    if ( element.action === 'entrada' ) {
                        for ( let i = 0; i < entranceList.length; i++ ) {
                            entranceList[i].value = element[ entranceList[i].period ];
                        }
                    }
                    else if ( element.action === 'saida' ) {
                        for ( let i = 0; i < exitList.length; i++ ) {
                            exitList[i].value = element[ exitList[i].period ];
                        }
                    }
                });

                let dayOfWeek = new Date( timestampFromInMilliseconds ).getDay();

                let entranceListInDateOrder = entranceList.splice( dayOfWeek );
                entranceList.forEach( d => entranceListInDateOrder.push( d ) );

                let exitListInDateOrder = exitList.splice( dayOfWeek );
                exitList.forEach( d => exitListInDateOrder.push( d ) );

                let returnPackage = {
                    entrance: entranceListInDateOrder,
                    exit: exitListInDateOrder,
                    timestampFrom: timestampFromInMilliseconds,
                    timestampTo: timestampToInMilliseconds
                };

                res.status(200);
                res.send( JSON.stringify( returnPackage ) );
            }
        });

});

app.get( '/v1/parkings/:id/findByMonth', ( req, res ) => {

    let parkingId = Number( req.params.id );
    let timestampInMilliseconds = Number( req.query.timestamp );
    let timestampFromInMilliseconds = extractDayTimestamp( timestampInMilliseconds );
    let timestampFromInSeconds = timestampFromInMilliseconds / 1000;
    let daysInMonth = monthDays( timestampFromInMilliseconds );
    let monthPeriodTimestampInMilliseconds = daysInMonth * ONE_DAY_TIMESTAMP_MILLISECONDS - 1;

    let timestampToInMilliseconds = adjustTimezoneTimestamp( timestampFromInMilliseconds,
            timestampFromInMilliseconds + monthPeriodTimestampInMilliseconds );

    timestampToInMilliseconds =
            adjustStartOfDaylightSaving( timestampInMilliseconds, timestampToInMilliseconds );

    let timestampToInSeconds = timestampToInMilliseconds / 1000;

    let query = "SELECT acao AS action";

    for ( let i = 0; i < daysInMonth; i++ ) {
        query += ", COUNT(CASE WHEN DAY(timestamp) = " + ( i + 1 ) + " THEN acao END) '"
                + ( i + 1 ) + "d'";
    }

    if ( parkingId === 0 ) { // Campus
        query += " FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) GROUP BY acao;";
    }
    else if ( parkingId <= 2 ) { // Portarias
        query += " FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) AND idportaria=? GROUP BY acao;";

    }
    else { // Bolsões
        query += " FROM tbl_bolsao WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) AND idbolsao=? GROUP BY acao;";
    }

    pool.query( query, [ timestampFromInSeconds, timestampToInSeconds, parkingId ],
        ( error, results, fields ) => {
            if ( error ) {
                res.status(400);
                res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
            }
            else {
                let entranceList = Array.apply( null, Array( daysInMonth ) ).map( ( e, i ) =>
                        e = { period: ( i + 1 ) + 'd', value: 0 } );
                let exitList = Array.apply( null, Array( daysInMonth ) ).map( ( e, i ) =>
                        e = { period: ( i + 1 ) + 'd', value: 0 } );

                results.forEach( element => {
                    if ( element.action === 'entrada' ) {
                        for ( let i = 0; i < entranceList.length; i++ ) {
                            entranceList[i].value = element[ entranceList[i].period ];
                        }
                    }
                    else if ( element.action === 'saida' ) {
                        for ( let i = 0; i < exitList.length; i++ ) {
                            exitList[i].value = element[ exitList[i].period ];
                        }
                    }
                });

                // Subtract one to match array zero based index
                let dayOfMonth = new Date( timestampFromInMilliseconds ).getDate() - 1;

                let entranceListInDateOrder = entranceList.splice( dayOfMonth );
                entranceList.forEach( d => entranceListInDateOrder.push( d ) );

                let exitListInDateOrder = exitList.splice( dayOfMonth );
                exitList.forEach( d => exitListInDateOrder.push( d ) );

                let returnPackage = {
                    entrance: entranceListInDateOrder,
                    exit: exitListInDateOrder,
                    timestampFrom: timestampFromInMilliseconds,
                    timestampTo: timestampToInMilliseconds
                };

                res.status(200);
                res.send( JSON.stringify( returnPackage ) );
            }
        });

});

app.get('/v1/parkings/:id/findByYear', ( req, res ) => {

    let parkingId = Number( req.params.id );
    let timestampFromInMilliseconds = extractMonthTimestamp( Number( req.query.timestamp ) );
    let timestampFromInSeconds = timestampFromInMilliseconds / 1000;
    let yearPeriodTimestampInMilliseconds = ONE_YEAR_TIMESTAMP_MILLISECONDS - 1;

    // If leap year and month until February, it's necessary to add one extra day to match
    // one leap year period timestamp
    if ( isLeapYear( timestampFromInMilliseconds ) &&
        isMonthUntilFebruary( timestampFromInMilliseconds ) ) {
        yearPeriodTimestampInMilliseconds += ONE_DAY_TIMESTAMP_MILLISECONDS;
    }

    let timestampToInMilliseconds = adjustTimezoneTimestamp( timestampFromInMilliseconds,
            timestampFromInMilliseconds + yearPeriodTimestampInMilliseconds );

    let timestampToInSeconds = timestampToInMilliseconds / 1000;

    let query = "SELECT acao AS action";

    for ( let i = 0; i < 12; i++ ) {
        query += ", COUNT(CASE WHEN MONTH(timestamp) = " + ( i + 1 ) + " THEN acao END) '"
                + nameOfMonth[i] + "'";
    }

    if ( parkingId === 0 ) { // Campus
        query += " FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) GROUP BY acao;";
    }
    else if ( parkingId <= 2 ) { // Portarias
        query += " FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) AND idportaria=? GROUP BY acao;";
    }
    else { // Bolsões
        query += "FROM tbl_bolsao WHERE timestamp BETWEEN FROM_UNIXTIME(?)"
                + " AND FROM_UNIXTIME(?) AND idbolsao=? GROUP BY acao;";
    }

    pool.query( query, [ timestampFromInSeconds, timestampToInSeconds, parkingId ],
        ( error, results, fields ) => {
            if ( error ) {
                res.status(400);
                res.send( JSON.stringify( { code: 400, type: 'error', message: error } ) );
            }
            else {
                let entranceList = Array.apply( null, Array(12) ).map( ( e, i ) =>
                        e = { period: nameOfMonth[i], value: 0 } );
                let exitList = Array.apply( null, Array(12) ).map( ( e, i ) =>
                        e = { period: nameOfMonth[i], value: 0 } );

                results.forEach( element => {
                    if ( element.action === 'entrada' ) {
                        for ( let i = 0; i < entranceList.length; i++ ) {
                            entranceList[i].value = element[ entranceList[i].period ];
                        }
                    }
                    else if ( element.action === 'saida' ) {
                        for ( let i = 0; i < exitList.length; i++ ) {
                            exitList[i].value = element[ exitList[i].period ];
                        }
                    }
                });

                let monthOfYear = new Date( timestampFromInMilliseconds ).getMonth();

                let entranceListInDateOrder = entranceList.splice( monthOfYear );
                entranceList.forEach( m => entranceListInDateOrder.push( m ) );

                let exitListInDateOrder = exitList.splice( monthOfYear );
                exitList.forEach( m => exitListInDateOrder.push( m ) );

                let returnPackage = {
                    entrance: entranceListInDateOrder,
                    exit: exitListInDateOrder,
                    timestampFrom: timestampFromInMilliseconds,
                    timestampTo: timestampToInMilliseconds
                };

                res.status(200);
                res.send(JSON.stringify(returnPackage));
            }
        });

});


const server = app.listen( port );
console.log('Express server listening at %s port', server.address().port);
