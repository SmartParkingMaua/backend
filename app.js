'use strict';

import express from 'express';
import morgan from 'morgan';
import { urlencoded, json } from 'body-parser';
import v1router from './v1/router'

const app = express();
const methodsAllowed = Object.freeze([ 'GET', 'POST' ]);


app.use( morgan('dev') );
app.use( urlencoded({ extended: false }) );
app.use( json() );

// Adding headers
app.use(( req, res, next ) => {
  res.header( 'Access-Control-Allow-Origin', '*' );
  res.header(
    'Access-Control-Allow-Headers',
    'Origin, X-Requested-With, Content-Type, Accept, Authorization'
  );

  if ( req.method === 'OPTIONS' ) {
    res.header( 'Access-Control-Allow-Headers', methodsAllowed.toString() );
    return res.status( 200 ).json({ });
  }

  next();
});

// Handling known routes
app.get( '/', ( req, res, next ) =>
  res.status( 200 ).json({ message: 'Welcome to SmartParking API' })
);
app.use( '/v1', v1router );

// Handling unknown routes
app.use(( req, res, next ) => {
  const error = new Error();

  if ( !methodsAllowed.includes( req.method ) ) {
    error.message = 'Request method not allowed.'
    error.status = 405;
  } else {
    error.message = 'Endpoint not found.';
    error.status = 404;
  }

  next( error );
});

// Handling errors
app.use(( err, req, res, next ) => {
  if ( Object.prototype.isPrototypeOf.call( Error.prototype, err ) ) {
    return res.status( err.status || 500 ).json({ error: err.message });
  }

  console.error('~~~ Unexpected error exception start ~~~')
  console.error( req );
  console.error( err );
  console.error('~~~ Unexpected error exception end ~~~');

  res.status( 500 ).json({
    error: 'An unexpected error occurred while processing your request.'
  });
});


export default app;
