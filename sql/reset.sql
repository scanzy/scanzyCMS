
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Contents;
DROP TABLE IF EXISTS Macros;
DROP TABLE IF EXISTS Templates;
DROP TABLE IF EXISTS Substitutions;
DROP TABLE IF EXISTS Files;

SET FOREIGN_KEY_CHECKS = 1;

DROP PROCEDURE IF EXISTS getFileContents;
DROP PROCEDURE IF EXISTS getContents;
DROP PROCEDURE IF EXISTS test;