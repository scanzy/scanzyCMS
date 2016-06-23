CREATE TABLE Contents (
    Id int,
    Text varchar(8191),
    TemplateId int,
    CacheTime int(8) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (ParentId) REFERENCES Templates(Id)
);
CREATE TABLE Substitutions (
    SearchId int NOT NULL,
    Macro varchar(31) NOT NULL,
    OrderIndex int NOT NULL,
    ReplaceId int NOT NULL,
    FOREIGN KEY (SearchId) REFERENCES Contents(Id),
    FOREIGN KEY (ReplaceId) REFERENCES Templates(Id)
);
CREATE TABLE Templates (
    Id int,
    Name varchar(31),
    Html varchar(8191),
    ContentId int,
    PRIMARY KEY (id),
    FOREIGN KEY (ContentId) REFERNCES Contents(id)
);
CREATE TABLE Files (
    Url varchar(31) UNIQUE NOT NULL,
    ContentId int NOT NULL,
    FOREIGN KEY (ContentId) REFERENCES Contents(Id)
);
CREATE TABLE Macros (
    SearchId int NOT NULL,
    Macro varchar(31) NOT NULL,
    ReplaceId int NOT NULL,
    FOREIGN KEY (SearchId) REFERENCES Templates(Id),
    FOREIGN KEY (SearchId) REFERENCES Templates(Id)
);