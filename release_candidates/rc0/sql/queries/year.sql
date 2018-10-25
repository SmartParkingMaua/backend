SELECT acao AS action,
	COUNT(CASE WHEN MONTH(timestamp) = 1 THEN acao END) 'Janeiro',
	COUNT(CASE WHEN MONTH(timestamp) = 2 THEN acao END) 'Fevereiro',
	COUNT(CASE WHEN MONTH(timestamp) = 3 THEN acao END) 'Março',
	COUNT(CASE WHEN MONTH(timestamp) = 4 THEN acao END) 'Abril',
	COUNT(CASE WHEN MONTH(timestamp) = 5 THEN acao END) 'Maio',
	COUNT(CASE WHEN MONTH(timestamp) = 6 THEN acao END) 'Junho',
	COUNT(CASE WHEN MONTH(timestamp) = 7 THEN acao END) 'Julho',
	COUNT(CASE WHEN MONTH(timestamp) = 8 THEN acao END) 'Agosto',
	COUNT(CASE WHEN MONTH(timestamp) = 9 THEN acao END) 'Setembro',
	COUNT(CASE WHEN MONTH(timestamp) = 10 THEN acao END) 'Outubro',
	COUNT(CASE WHEN MONTH(timestamp) = 11 THEN acao END) 'Novembro',
	COUNT(CASE WHEN MONTH(timestamp) = 12 THEN acao END) 'Dezembro'
FROM tbl_portaria WHERE timestamp BETWEEN FROM_UNIXTIME(1508464800)
AND FROM_UNIXTIME(1540000799) GROUP BY acao;
