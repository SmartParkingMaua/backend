SELECT acao AS action,
    COUNT(CASE WHEN HOUR(timestamp) = 0 THEN acao END) '1h',
    COUNT(CASE WHEN HOUR(timestamp) = 1 THEN acao END) '2h',
    COUNT(CASE WHEN HOUR(timestamp) = 2 THEN acao END) '3h',
    COUNT(CASE WHEN HOUR(timestamp) = 3 THEN acao END) '4h',
    COUNT(CASE WHEN HOUR(timestamp) = 4 THEN acao END) '5h',
    COUNT(CASE WHEN HOUR(timestamp) = 5 THEN acao END) '6h',
    COUNT(CASE WHEN HOUR(timestamp) = 6 THEN acao END) '7h',
    COUNT(CASE WHEN HOUR(timestamp) = 7 THEN acao END) '8h',
    COUNT(CASE WHEN HOUR(timestamp) = 8 THEN acao END) '9h',
    COUNT(CASE WHEN HOUR(timestamp) = 9 THEN acao END) '10h',
    COUNT(CASE WHEN HOUR(timestamp) = 10 THEN acao END) '11h',
    COUNT(CASE WHEN HOUR(timestamp) = 11 THEN acao END) '12h',
    COUNT(CASE WHEN HOUR(timestamp) = 12 THEN acao END) '13h',
    COUNT(CASE WHEN HOUR(timestamp) = 13 THEN acao END) '14h',
    COUNT(CASE WHEN HOUR(timestamp) = 14 THEN acao END) '15h', 
    COUNT(CASE WHEN HOUR(timestamp) = 15 THEN acao END) '16h',
    COUNT(CASE WHEN HOUR(timestamp) = 16 THEN acao END) '17h',
    COUNT(CASE WHEN HOUR(timestamp) = 17 THEN acao END) '18h',
    COUNT(CASE WHEN HOUR(timestamp) = 18 THEN acao END) '19h', 
    COUNT(CASE WHEN HOUR(timestamp) = 19 THEN acao END) '20h',
    COUNT(CASE WHEN HOUR(timestamp) = 20 THEN acao END) '21h',
    COUNT(CASE WHEN HOUR(timestamp) = 21 THEN acao END) '22h',
    COUNT(CASE WHEN HOUR(timestamp) = 22 THEN acao END) '23h',
    COUNT(CASE WHEN HOUR(timestamp) = 23 THEN acao END) '24h'
FROM tbl_portaria WHERE DATE(timestamp) = DATE(FROM_UNIXTIME(1508276400)) GROUP BY acao