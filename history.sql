CREATE TABLE Job (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, expiresAt DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE Gallery (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE StaticContent (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE Image (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, thumbname VARCHAR(255) NOT NULL, INDEX IDX_4FC2B5B3DA5256D (image_id), PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE Download (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL, serverFileName VARCHAR(255) NOT NULL, mimeType VARCHAR(255) NOT NULL, originalFileName VARCHAR(255) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE BlogPost (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(250) NOT NULL, content VARCHAR(255) NOT NULL, publishedAt DATETIME NOT NULL, createdAt DATETIME NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE Member (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, password VARCHAR(40) NOT NULL, salt VARCHAR(32) NOT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', registrationKey VARCHAR(32) NOT NULL, forumId INT NOT NULL, wikiId INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB;
CREATE TABLE Email (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_265353707597D3FE (member_id), PRIMARY KEY(id)) ENGINE = InnoDB;
ALTER TABLE Image ADD CONSTRAINT FK_4FC2B5B3DA5256D FOREIGN KEY (image_id) REFERENCES Gallery(id);
ALTER TABLE Email ADD CONSTRAINT FK_265353707597D3FE FOREIGN KEY (member_id) REFERENCES Member(id)
;
