SELECT acao AS action,
	COUNT(CASE WHEN DAY(timestamp) = 1 THEN acao END) '1d',
	COUNT(CASE WHEN DAY(timestamp) = 2 THEN acao END) '2d',
	COUNT(CASE WHEN DAY(timestamp) = 3 THEN acao END) '3d',
	COUNT(CASE WHEN DAY(timestamp) = 4 THEN acao END) '4d',
	COUNT(CASE WHEN DAY(timestamp) = 5 THEN acao END) '5d',
	COUNT(CASE WHEN DAY(timestamp) = 6 THEN acao END) '6d',
	COUNT(CASE WHEN DAY(timestamp) = 7 THEN acao END) '7d',
	COUNT(CASE WHEN DAY(timestamp) = 8 THEN acao END) '8d',
	COUNT(CASE WHEN DAY(timestamp) = 9 THEN acao END) '9d',
	COUNT(CASE WHEN DAY(timestamp) = 10 THEN acao END) '10d',
	COUNT(CASE WHEN DAY(timestamp) = 11 THEN acao END) '11d',
	COUNT(CASE WHEN DAY(timestamp) = 12 THEN acao END) '12d',
	COUNT(CASE WHEN DAY(timestamp) = 13 THEN acao END) '13d',
	COUNT(CASE WHEN DAY(timestamp) = 14 THEN acao END) '14d',
	COUNT(CASE WHEN DAY(timestamp) = 15 THEN acao END) '15d',
	COUNT(CASE WHEN DAY(timestamp) = 16 THEN acao END) '16d',
	COUNT(CASE WHEN DAY(timestamp) = 17 THEN acao END) '17d',
	COUNT(CASE WHEN DAY(timestamp) = 18 THEN acao END) '18d',
	COUNT(CASE WHEN DAY(timestamp) = 19 THEN acao END) '19d',
	COUNT(CASE WHEN DAY(timestamp) = 20 THEN acao END) '20d',
	COUNT(CASE WHEN DAY(timestamp) = 21 THEN acao END) '21d',
	COUNT(CASE WHEN DAY(timestamp) = 22 THEN acao END) '22d',
	COUNT(CASE WHEN DAY(timestamp) = 23 THEN acao END) '23d',
	COUNT(CASE WHEN DAY(timestamp) = 24 THEN acao END) '24d',
	COUNT(CASE WHEN DAY(timestamp) = 25 THEN acao END) '25d',
	COUNT(CASE WHEN DAY(timestamp) = 26 THEN acao END) '26d',
	COUNT(CASE WHEN DAY(timestamp) = 27 THEN acao END) '27d',
	COUNT(CASE WHEN DAY(timestamp) = 28 THEN acao END) '28d',
	COUNT(CASE WHEN DAY(timestamp) = 29 THEN acao END) '29d',
	COUNT(CASE WHEN DAY(timestamp) = 30 THEN acao END) '30d',
	COUNT(CASE WHEN DAY(timestamp) = 31 THEN acao END) '31d'
FROM tbl_portaria WHERE idportaria=1 AND timestamp BETWEEN FROM_UNIXTIME(1508464800)
AND FROM_UNIXTIME(1511143200) GROUP BY acao;
