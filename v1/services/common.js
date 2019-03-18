'use strict';

import { Gate, Pocket } from '../models/Action';

export const getModel = ( idParking ) => {
  return ( idParking <= 2 ) ? Gate : Pocket;
}
