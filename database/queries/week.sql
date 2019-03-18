SELECT `action` AS `action`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 1 THEN action END) AS `Domingo`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 2 THEN action END) AS `Segunda`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 3 THEN action END) AS `Terça`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 4 THEN action END) AS `Quarta`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 5 THEN action END) AS `Quinta`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 6 THEN action END) AS `Sexta`,
COUNT(CASE WHEN DAYOFWEEK(createdAt) = 7 THEN action END) AS `Sábado`
FROM `gates` AS `gate`
WHERE (`gate`.`idParking` = 1 OR `gate`.`idParking` = 2) AND `gate`.`createdAt` BETWEEN '1993-10-28 00:00:00' AND '1993-11-03 23:59:59'
GROUP BY `action`;
