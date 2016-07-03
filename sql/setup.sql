-- tables setup

CREATE TABLE IF NOT EXISTS Contents (
    Id int,
    Text varchar(8191) NOT NULL DEFAULT '',
    TemplateId int NOT NULL,
    CacheTime int(8) NOT NULL,
    PRIMARY KEY (Id)    
);

CREATE TABLE IF NOT EXISTS Templates (
    Id int,
    Name varchar(31) UNIQUE NOT NULL,
    Html varchar(8191) NOT NULL DEFAULT '',
    ContentId int,
    PRIMARY KEY (Id),
    FOREIGN KEY (ContentId) REFERENCES Contents(Id)
);

CREATE TABLE IF NOT EXISTS Substitutions (
    SearchId int NOT NULL,
    Macro varchar(31) NOT NULL,
    OrderIndex int NOT NULL,
    ReplaceId int NOT NULL,
    FOREIGN KEY (SearchId) REFERENCES Contents(Id),
    FOREIGN KEY (ReplaceId) REFERENCES Templates(Id)
);

CREATE TABLE IF NOT EXISTS Files (
    Url varchar(31) UNIQUE NOT NULL,
    ContentId int NOT NULL,
    FOREIGN KEY (ContentId) REFERENCES Contents(Id)
);

CREATE TABLE IF NOT EXISTS Macros (
    SearchId int NOT NULL,
    Macro varchar(31) NOT NULL,
    ReplaceId int NOT NULL,
    FOREIGN KEY (SearchId) REFERENCES Templates(Id),
    FOREIGN KEY (ReplaceId) REFERENCES Templates(Id)
);

ALTER TABLE Contents ADD CONSTRAINT 
FOREIGN KEY (TemplateId) REFERENCES Templates(Id);

-- procedures setup

DROP PROCEDURE IF EXISTS getContents;
CREATE PROCEDURE getContents(
    OUT output VARCHAR(8191),
    id INT, 
    prefix VARCHAR(31), 
    suffix VARCHAR(31)
) 
BEGIN
    DECLARE tempId INT; -- template id

    DECLARE n INT; -- used in for loop
    DECLARE i INT; 

    DECLARE macroName VARCHAR(31); -- macro to find
    DECLARE idReplace INT; -- index of content to replace
    DECLARE txtReplace VARCHAR(8191); -- text to replace
    
    SELECT TemplateId FROM Contents WHERE Contents.Id = id INTO tempId; -- gets template id
    IF tempId IS NULL THEN
        SELECT Text FROM Contents WHERE Contents.Id = id INTO output; -- returns direct text
    ELSE
        SELECT Html From Templates WHERE Templates.Id = tempId INTO output; -- gets html        

        SELECT COUNT(*) FROM Substitutions WHERE SearchId = id INTO n; -- counts macros

        SET i = 0; WHILE i < n DO -- for loop

            SELECT Macro, ReplaceId INTO macroName, idReplace -- gets macro and replace id for this template
                FROM Substitutions WHERE SearchId = id ORDER BY OrderIndex LIMIT i , 1; 

            -- gets (recursively) text to replace and replaces
            CALL getContents(txtReplace, idReplace, prefix, suffix);
            SET output = REPLACE(output, CONCAT(prefix, macroName, suffix), txtReplace);

            SET i = i + 1;
        END WHILE;
    END IF;
END;

DROP PROCEDURE IF EXISTS getFileContents;
CREATE PROCEDURE getFileContents(url VARCHAR(127), prefix VARCHAR(31), suffix VARCHAR(31))
BEGIN
    DECLARE output VARCHAR(8191);
    SET MAX_SP_RECURSION_DEPTH = 100; -- enables recursion

    CALL getContents(output, (SELECT ContentId FROM Files WHERE Files.Url = url), prefix, suffix);
    SELECT output;
END;