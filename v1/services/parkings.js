'use strict';

import { Op } from 'sequelize';
import Parking from '../models/Parking';
import { getModel } from './common';

export const getAllParkings = () => {
  return Parking.findAll({
    attributes: [ 'id', 'name', 'occupiedLots', 'maxLots' ]
  });
}

export const buildWhereFilter = ( idParking, createdAtFrom, createdAtTo ) => {
  if ( idParking === 0 ) {
    idParking = {
      [ Op.or ]: [ 1, 2 ]
    }
  }

  return {
    idParking: idParking,
    createdAt: {
      [ Op.between ]: [ createdAtFrom, createdAtTo ]
    }
  }
}

export const getParkingActions = ( idParking, attributes, where ) => {
  const Model = getModel( idParking );

  return Model
    .findAll({
      attributes: attributes,
      where: where,
      group: 'action'
    })
    // For some unknown reason, when trying to access something like
    // results[0]['5m'] without have stringified and then parsed
    // the returned results, the value isn't retrieved properly.
    // However, if we try to get results[0]['action'] it works fine.
    // This might be an issue in Sequelize tool, possibly.
    .then( results => JSON.parse( JSON.stringify( results ) ) );
}

export const buildReturnActions = ( results ) => {
  let actions = {};

  results.map(({ action, ...values }) => {
    let data = [];

    for ( let period in values ) {
      data.push({ period: period, value: values[ period ] });
    }

    actions[ action ] = data;
  });

  return {
    entrance: actions.entrance,
    exit: actions.exit
  };
}

export const buildReturnActionsInOrder = ( results, orderStart ) => {
  let actions = {};

  results.map(({ action, ...values }) => {
    let data = [];

    for ( let period in values ) {
      data.push({ period: period, value: values[ period ] });
    }

    let dataInDateOrder = data.splice( orderStart );
    data.forEach( d => dataInDateOrder.push( d ) );

    actions[ action ] = dataInDateOrder;
  });

  return {
    entrance: actions.entrance,
    exit: actions.exit
  };
}

export const makeResultsCompatible = (
  results,
  startIndex,
  endIndex,
  indexIncrement,
  prefixLabel,
  isLabelArray
) => {
  if ( !results.find( e => e.action === 'entrance' ) ) {
    results.push(
      addFakeResults(
        { action: 'entrance' },
        startIndex,
        endIndex,
        indexIncrement,
        prefixLabel,
        isLabelArray
      )
    );
  }

  if ( !results.find( e => e.action === 'exit' ) ) {
    results.push(
      addFakeResults(
        { action: 'exit' },
        startIndex,
        endIndex,
        indexIncrement,
        prefixLabel,
        isLabelArray
      )
    );
  }

  return results;
}

const addFakeResults = (
  element,
  startIndex,
  endIndex,
  indexIncrement,
  prefixLabel,
  isLabelArray
) => {
  for ( let i = startIndex; i < endIndex; i += indexIncrement ) {
    if ( isLabelArray ) {
      element[ prefixLabel[ i ] ] = 0;
    }
    else {
      element[ i + prefixLabel ] = 0;
    }
  }

  return element;
}
