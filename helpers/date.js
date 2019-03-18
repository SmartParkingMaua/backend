'use strict';

// export const SECOND_TIMESTAMP = 1000;
// export const MINUTE_TIMESTAMP = 60000;
// export const HOUR_TIMESTAMP = 3600000;
// export const DAY_TIMESTAMP = 86400000;
// export const WEEK_TIMESTAMP = 604800000;
// export const YEAR_TIMESTAMP = 31536000000;

export const dayOfWeek =  [
  'Domingo',
  'Segunda',
  'Terça',
  'Quarta',
  'Quinta',
  'Sexta',
  'Sábado'
];

export const monthOfYear = [
  'Janeiro',
  'Fevereiro',
  'Março',
  'Abril',
  'Maio',
  'Junho',
  'Julho',
  'Agosto',
  'Setembro',
  'Outubro',
  'Novembro',
  'Dezembro'
];

Date.prototype.extractHour = function() {
  this.setMinutes( 0 );
  this.setSeconds( 0 );
  this.setMilliseconds( 0 );
  return this;
}

Date.prototype.extractDay = function() {
  this.extractHour();
  this.setHours( 0 );
  return this;
}

Date.prototype.extractMonth = function() {
  this.extractDay();
  this.setDate( 1 );
  return this;
}

Date.prototype.monthDays = function() {
  return new Date( this.getFullYear(), this.getMonth() + 1, 0 ).getDate();
}

Date.prototype.nextHour = function() {
  return new Date( this ).setHours( this.getHours() + 1 );
}

Date.prototype.nextDay = function() {
  return new Date( this ).setDate( this.getDate() + 1 );
}

Date.prototype.nextWeek = function() {
  return new Date( this ).setDate( this.getDate() + 7 );
}

Date.prototype.nextMonth = function() {
  return new Date( this ).setMonth( this.getMonth() + 1 );
}

Date.prototype.nextYear = function() {
  return new Date( this ).setFullYear( this.getFullYear() + 1 );
}
