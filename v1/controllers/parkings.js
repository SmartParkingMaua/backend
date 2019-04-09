'use strict';

import Sequelize from 'sequelize';
import { dayOfWeek, monthOfYear } from '../../helpers/date';
import { 
  getAllParkings as getAllParkingsService,
  buildWhereFilter,
  getParkingActions,
  buildReturnActions,
  buildReturnActionsInOrder,
  makeResultsCompatible
} from '../services/parkings';

export const getAllParkings = async () => {
  const parkings = await getAllParkingsService();

  return Promise.resolve()
    .then(() => {
      return {
          parkings: parkings
      }
    });
}

export const getParkingByHour = async ( idParking, timestamp ) => {
  const createdAtFrom = new Date( timestamp ).extractHour();
  const createdAtTo = new Date( createdAtFrom.nextHour() - 1 );
  let results;
  let actions;
  
  // Building SQL query
  const where = buildWhereFilter( idParking, createdAtFrom, createdAtTo );
  const attributes = [[ 'action', 'action' ]];

  for ( let i = 0; i < 60; i+=5 ) {
    let query = `CASE WHEN MINUTE(createdAt) BETWEEN ${i} AND ${i + 4} ` +
      'THEN action END';
    let alias = `${i}m`;

    attributes.push([
      Sequelize.fn( 'COUNT', Sequelize.literal( query ) ),
      alias
    ]);
  }

  results = await getParkingActions( idParking, attributes, where );
  results = makeResultsCompatible( results, 0, 60, 5, 'm', false );

  actions = buildReturnActions( results );

  return Promise.resolve()
    .then(() => {
      return {
        actions: actions,
        timestamp: {
          from: createdAtFrom.getTime(),
          to: createdAtTo.getTime()
        }
      }
    });
}

export const getParkingByDay = async ( idParking, timestamp ) => {
  const createdAtFrom = new Date( timestamp ).extractDay();
  const createdAtTo = new Date( createdAtFrom.nextDay() - 1 );
  let results;
  let actions;

  // Building SQL query
  const where = buildWhereFilter( idParking, createdAtFrom, createdAtTo );
  const attributes = [[ 'action', 'action' ]];

  for ( let i = 0; i < 24; i++ ) {
    let query = `CASE WHEN HOUR(createdAt) = ${i} THEN action END`;
    let alias = `${i}h`;

    attributes.push([
      Sequelize.fn( 'COUNT', Sequelize.literal( query ) ),
      alias
    ]);
  }

  results = await getParkingActions( idParking, attributes, where );
  results = makeResultsCompatible( results, 0, 24, 1, 'h', false );

  actions = buildReturnActions( results );

  return Promise.resolve()
    .then(() => {
      return {
        actions: actions,
        timestamp: {
          from: createdAtFrom.getTime(),
          to: createdAtTo.getTime()
        }
      }
    });
}

export const getParkingByWeek = async ( idParking, timestamp ) => {
  const createdAtFrom = new Date( timestamp ).extractDay();
  const createdAtTo = new Date( createdAtFrom.nextWeek() - 1 );
  let results;
  let actions;

  // Building SQL query
  const where = buildWhereFilter( idParking, createdAtFrom, createdAtTo );
  const attributes = [[ 'action', 'action' ]];

  for ( let i = 1; i <= 7; i++ ) {
    let query = `CASE WHEN DAYOFWEEK(createdAt) = ${i} THEN action END`;
    let alias = dayOfWeek[ i - 1 ];

    attributes.push([
      Sequelize.fn( 'COUNT', Sequelize.literal( query ) ),
      alias
    ]);
  }

  results = await getParkingActions( idParking, attributes, where );
  results = makeResultsCompatible( results, 0, 7, 1, dayOfWeek, true );

  actions = buildReturnActionsInOrder( results, createdAtFrom.getDay() );

  return Promise.resolve()
    .then(() => {
      return {
        actions: actions,
        timestamp: {
          from: createdAtFrom.getTime(),
          to: createdAtTo.getTime()
        }
      }
    });
}

export const getParkingByMonth = async ( idParking, timestamp ) => {
  const createdAtFrom = new Date( timestamp ).extractDay();
  const createdAtTo = new Date( createdAtFrom.nextMonth() - 1 );
  const monthDays = createdAtFrom.monthDays();
  let results;
  let actions;
  
  // Building SQL query
  const where = buildWhereFilter( idParking, createdAtFrom, createdAtTo );
  const attributes = [[ 'action', 'action' ]];

  for ( let i = 1; i <= monthDays; i++ ) {
    let query = `CASE WHEN DAY(createdAt) = ${i} THEN action END`;
    let alias = `${i}d`;

    attributes.push([
      Sequelize.fn( 'COUNT', Sequelize.literal( query ) ),
      alias
    ]);
  }

  results = await getParkingActions( idParking, attributes, where );
  results = makeResultsCompatible( results, 1, monthDays + 1, 1, 'd', false );

  actions = buildReturnActionsInOrder( results, createdAtFrom.getDate() - 1 );

  return Promise.resolve()
    .then(() => {
      return {
        actions: actions,
        timestamp: {
          from: createdAtFrom.getTime(),
          to: createdAtTo.getTime()
        }
      }
    });
}

export const getParkingByYear = async ( idParking, timestamp ) => {
  const createdAtFrom = new Date( timestamp ).extractMonth();
  const createdAtTo = new Date( createdAtFrom.nextYear() - 1 );
  let results;
  let actions;

  // Building SQL query
  const where = buildWhereFilter( idParking, createdAtFrom, createdAtTo );
  const attributes = [[ 'action', 'action' ]];

  for ( let i = 1; i <= 12; i++ ) {
    let query = `CASE WHEN MONTH(createdAt) = ${i} THEN action END`;
    let alias = monthOfYear[ i - 1 ];

    attributes.push([
      Sequelize.fn( 'COUNT', Sequelize.literal( query ) ),
      alias
    ]);
  }

  results = await getParkingActions( idParking, attributes, where );
  results = makeResultsCompatible( results, 0, 12, 1, monthOfYear, true );

  actions = buildReturnActionsInOrder( results, createdAtFrom.getMonth() );

  return Promise.resolve()
    .then(() => {
      return {
        actions: actions,
        timestamp: {
          from: createdAtFrom.getTime(),
          to: createdAtTo.getTime()
        }
      }
    });
}
