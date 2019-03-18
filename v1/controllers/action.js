'use strict';

import { addAction as addActionService } from '../services/action';

export const addAction = async ( idParking, action, timestamp ) => {
  const createdAt = new Date( timestamp );

  const addedAction = await addActionService( idParking, action, createdAt );

  return Promise.resolve()
    .then(() => {
      return {
        status: 201,
        message: 'Action added',
        action: addedAction
      }
    });
}
