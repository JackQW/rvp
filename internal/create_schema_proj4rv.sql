SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `proj4rv` ;
USE `proj4rv` ;

-- -----------------------------------------------------
-- Table `proj4rv`.`user`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `proj4rv`.`user` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `username` CHAR(16) NOT NULL ,
  `password` BINARY(16) NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `first_name` VARCHAR(255) NOT NULL ,
  `last_name` VARCHAR(255) NOT NULL ,
  `city` VARCHAR(255) NOT NULL ,
  `state` CHAR(2) NOT NULL ,
  `zip` CHAR(10) NOT NULL COMMENT 'zip could be NUMERIC(9) or CHAR(9) if necessary, by omitting -, but sorting would be funny.' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) )
ENGINE = InnoDB;

USE `proj4rv` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
