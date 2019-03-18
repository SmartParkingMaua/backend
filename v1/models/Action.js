'use strict';

import { INTEGER, DATE, STRING } from 'sequelize';
import db from '../../config/database';
import Parking from './Parking';

const tableStructure = {
  idParking: {
    type: INTEGER,
    required: true,
    validate: {
      isValidId( value ) {
        if ( Number.isNaN( value ) || value <= 0 ) {
          const error = new Error(
              'Invalid idParking value. Try a value higher than 0.'
            );

          error.status = 400;

          throw error;
        }
      }
    }
  },
  action: {
    type: STRING,
    required: true,
    validate: {
      isValidAction( value ) {
        if ( value !== 'entrance' && value !== 'exit' ) {
          const error =  new Error(
              'Invalid action value. Try \'entrance\' or \'exit\'.'
            );

          error.status = 400;

          throw error;
        }
      }
    }
  },
  createdAt: {
    type: DATE,
    required: true,
    validate: {
      isValidTimestamp( value ) {
        if ( Number.isNaN( value ) || value < 0 ) {
          const error = new Error(
              'Invalid timestamp value. Try a value higher or equal 0.'
            );

          error.status = 400;

          throw error;
        }
      }
    }
  }
};

const commonSettings = {
  timestamps: true,
  updatedAt: false
};

const Gate = db.define( 'gate', tableStructure, commonSettings );

const Pocket = db.define( 'pocket', tableStructure, commonSettings );

Parking.hasMany( Gate, { foreignKey: 'fk_gates_id' } );
Gate.belongsTo( Parking, { foreignKey: 'fk_gates_id' } );

Parking.hasMany( Pocket, { foreignKey: 'fk_pockets_id' } );
Pocket.belongsTo( Parking, { foreignKey: 'fk_pockets_id' } );


export {
  Gate,
  Pocket
}
