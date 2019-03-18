'use strict';

import { getModel } from './common';

export const addAction = ( idParking, action, createdAt ) => {
  const Model = getModel( idParking );

  return Model.create({
    idParking: idParking,
    action: action,
    createdAt: createdAt
  });
}
