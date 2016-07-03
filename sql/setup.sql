-- tables setup

CREATE TABLE IF NOT EXISTS Contents (
    Id int,
    Text varchar(8191) NOT NULL DEFAULT '',
    TemplateId int DEFAULT NULL,
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
    suffix VARCHAR(31),
    lastmod INT
) 
BEGIN
    DECLARE tempId INT; -- template id

    DECLARE n INT; -- used in for loop
    DECLARE i INT; 

    DECLARE macroName VARCHAR(31); -- macro to find
    DECLARE idReplace INT; -- index of content to replace
    DECLARE txtReplace VARCHAR(8191); -- text to replace 

    SELECT TemplateId FROM Contents WHERE Contents.Id = id INTO tempId; -- gets template id

    IF tempId IS NOT NULL THEN -- if not direct text checks cache

        -- if cache earlier than last db modification
        IF ((SELECT CacheTime FROM Contents WHERE Contents.Id = id) < lastmod) THEN

            SELECT Html From Templates WHERE Templates.Id = tempId INTO output; -- gets html        

            SELECT COUNT(*) FROM Substitutions WHERE SearchId = id INTO n; -- counts macros

            SET i = 0; WHILE i < n DO -- for loop

                SELECT Macro, ReplaceId INTO macroName, idReplace -- gets macro and replace id for this template
                    FROM Substitutions WHERE SearchId = id ORDER BY OrderIndex LIMIT i , 1; 

                -- gets (recursively) text to replace and replaces
                CALL getContents(txtReplace, idReplace, prefix, suffix, lastmod);
                SET output = REPLACE(output, CONCAT(prefix, macroName, suffix), txtReplace);

                SET i = i + 1;
            END WHILE;

            -- caches output
            UPDATE Contents SET Text = output, CacheTime = UNIX_TIMESTAMP() WHERE Contents.Id = id;
        END IF;

    ELSE -- if template null (direct text) or already cached
        SELECT Text FROM Contents WHERE Contents.Id = id INTO output; -- returns direct text    
    END IF;
END;

DROP PROCEDURE IF EXISTS getFileContents;
CREATE PROCEDURE getFileContents(url VARCHAR(127), prefix VARCHAR(31), suffix VARCHAR(31), lastmod INT)
BEGIN
    DECLARE output VARCHAR(8191);
    SET MAX_SP_RECURSION_DEPTH = 100; -- enables recursion

    CALL getContents(output, (SELECT ContentId FROM Files WHERE Files.Url = url), prefix, suffix, lastmod);
    SELECT output as 'html';
END;

DROP PROCEDURE IF EXISTS test;
CREATE PROCEDURE test()
BEGIN
    DECLARE x INT;   
    SELECT COUNT(*) FROM Contents INTO x;
    SELECT COUNT(*) FROM Macros INTO x;
    SELECT COUNT(*) FROM Templates INTO x;
    SELECT COUNT(*) FROM Substitutions INTO x;
    SELECT COUNT(*) FROM Files INTO x;
    CALL getFileContents('a', 'b', 'c', 0);
END;