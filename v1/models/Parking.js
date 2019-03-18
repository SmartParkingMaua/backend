'use strict';

import { INTEGER, STRING } from 'sequelize';
import db from '../../config/database';

const Parking = db.define( 'parking', {
    id: {
      type: INTEGER,
      primaryKey: true,
      required: true,
      validate: {
        isValidId( value ) {
          if ( Number.isNaN( value ) || value < 0 ) {
            const error = new Error(
                'Invalid id value. Try a value higher or equal 0.'
              );

            error.status = 400;

            throw error;
          }
        }
      }
    },
    name: {
      type: STRING,
      unique: true,
      required: true
    },
    occupiedLots: {
      type: INTEGER,
      required: true,
      validate: {
        isValidEntry( value ) {
          if ( Number.isNaN( value ) || value < 0 ) {
            const error = new Error(
                'Invalid occupiedLots value. Try a value higher or equal 0.'
              );

            error.status = 400;

            throw error;
          }
        }
      }
    },
    maxLots: {
      type: INTEGER,
      required: true,
      validate: {
        isValidEntry( value ) {
          if ( Number.isNaN( value ) || value < 0 ) {
            const error = new Error(
                'Invalid maxLots value. Try a value higher or equal 0.'
              );

            error.status = 400;

            throw error;
          }
        },
        isHigherThanOccupiedLots( value ) {
          if ( value < this.occupiedLots ) {
            const error = new Error(
                'Invalid maxLots value. Try a value higher or equal '+
                'occupiedLots value.'
              );

            error.status = 400;

            throw error;
          }
        }
      }
    }
  }, {
    timestamps: false
  }
);


export default Parking;
