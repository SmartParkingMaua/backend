
const checkParkingsParams = ( req, res, next ) => {
  let error = validateParkingId( +req.params.idParking );

  if ( error ) {
    return next( error );
  }

  error = validateTimestamp( +req.query.timestamp );

  if ( error ) {
    return next( error );
  }

  next();
}

const validateParkingId = ( idParking ) => {
  let error;

  if ( Number.isNaN( idParking ) || idParking < 0 ) {
    error = new Error(
        'Invalid idParking value. Try a value higher or equal 0.'
      );

    error.status = 400;
  }

  return error;
}

const validateTimestamp = ( desiredTimestamp ) => {
  let error;

  const timestamp = {
    start: new Date( process.env.DB_START_DATETIME ).getTime(),
    now: new Date().getTime(),
    desired: desiredTimestamp
  };

  // If the request specifies a timestamp where there's no entries,
  // Sequelize won't generate an error. Yet, it should be treated like an
  // error, since there's no resource available in that specific moment.
  const isValidPeriod =
    timestamp.desired >= timestamp.start && timestamp.desired <= timestamp.now;

  if ( Number.isNaN( timestamp.desired ) || !isValidPeriod ) {
    error = new Error(
      'There\'s no entries available in the timestamp specified.'
    );
  
    error.status = 400;
  }

  return error;
}


export default checkParkingsParams;
