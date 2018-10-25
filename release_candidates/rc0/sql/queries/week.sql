SELECT acao AS action,
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 1 THEN acao END) 'Domingo',
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 2 THEN acao END) 'Segunda',
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 3 THEN acao END) 'Terca',
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 4 THEN acao END) 'Quarta',
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 5 THEN acao END) 'Quinta',
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 6 THEN acao END) 'Sexta',
	COUNT(CASE WHEN DAYOFWEEK(timestamp) = 7 THEN acao END) 'Sabado'
FROM tbl_portaria WHERE idportaria=1 AND timestamp BETWEEN FROM_UNIXTIME(1508464800)
AND FROM_UNIXTIME(1509069600) GROUP BY acao;
