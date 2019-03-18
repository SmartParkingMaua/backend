'use strict';

import { Router } from 'express';
import checkParkingsParams from '../middleware/checkParkingsParams';
import { addAction } from './controllers/action';
import {
  getAllParkings,
  getParkingByHour,
  getParkingByDay,
  getParkingByWeek,
  getParkingByMonth,
  getParkingByYear
} from './controllers/parkings';

const router = Router();

const controllerHandler = ( promise, params ) => async ( req, res, next ) => {
  const boundParams = params ? params( req, res, next ) : [];

  try {
    let { status, ...result } = await promise( ...boundParams );
    return res.status( status || 200 ).json( result || { message: 'OK' } );
  }
  catch ( error ) {
    return next( error );
  }
};


router.post( '/action', controllerHandler( addAction, ( req, res, next ) =>
    [ +req.body.idParking, req.body.action, +req.body.timestamp ]
  )
);

router.get( '/parkings', controllerHandler( getAllParkings ) );

router.get(
  '/parkings/:idParking/hour',
  checkParkingsParams,
  controllerHandler( getParkingByHour, ( req, res, next ) =>
      [ +req.params.idParking, +req.query.timestamp ]
  )
);

router.get(
  '/parkings/:idParking/day',
  checkParkingsParams,
  controllerHandler( getParkingByDay, ( req, res, next ) =>
      [ +req.params.idParking, +req.query.timestamp ]
  )
);

router.get(
  '/parkings/:idParking/week',
  checkParkingsParams,
  controllerHandler( getParkingByWeek, ( req, res, next ) =>
      [ +req.params.idParking, +req.query.timestamp ]
  )
);

router.get(
  '/parkings/:idParking/month',
  checkParkingsParams,
  controllerHandler( getParkingByMonth, ( req, res, next ) =>
      [ +req.params.idParking, +req.query.timestamp ]
  )
);

router.get(
  '/parkings/:idParking/year',
  checkParkingsParams,
  controllerHandler( getParkingByYear, ( req, res, next ) =>
      [ +req.params.idParking, +req.query.timestamp ]
  )
);


export default router;
