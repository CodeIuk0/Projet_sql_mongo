-- MySQL Script generated by MySQL Workbench
-- Wed Apr 26 10:10:29 2023
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`books`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`books` ;

CREATE TABLE IF NOT EXISTS `mydb`.`books` (
  `book_id` VARCHAR(256) NOT NULL,
  `title` VARCHAR(256) NULL DEFAULT NULL,
  `pages` INT(11) NULL DEFAULT NULL,
  `summary` LONGTEXT NULL DEFAULT NULL,
  `tags` VARCHAR(45) NULL DEFAULT NULL,
  `editors` LONGTEXT NULL DEFAULT NULL,
  `Bookscol` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`book_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mydb`.`comment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`comment` ;

CREATE TABLE IF NOT EXISTS `mydb`.`comment` (
  `comment_id` VARCHAR(256) NOT NULL,
  `comment` VARCHAR(256) NULL DEFAULT NULL,
  `note` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`comment_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mydb`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`users` ;

CREATE TABLE IF NOT EXISTS `mydb`.`users` (
  `user_id` VARCHAR(256) NOT NULL,
  `user_name` VARCHAR(256) NULL DEFAULT NULL,
  `password_hash` VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mydb`.`librairie`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`librairie` ;

CREATE TABLE IF NOT EXISTS `mydb`.`librairie` (
  `command_id` VARCHAR(256) NOT NULL,
  `advancement` INT(11) NULL DEFAULT NULL,
  `comment_id` VARCHAR(256) NOT NULL,
  `user_id` VARCHAR(256) NOT NULL,
  `book_id` VARCHAR(256) NULL,
  PRIMARY KEY (`command_id`, `comment_id`, `user_id`),
  INDEX `fk_librairie_Comment1_idx` (`comment_id` ASC) VISIBLE,
  INDEX `fk_librairie_Users1_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_librairie_books1_idx` (`book_id` ASC) VISIBLE,
  CONSTRAINT `fk_librairie_Comment1`
    FOREIGN KEY (`comment_id`)
    REFERENCES `mydb`.`comment` (`comment_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_librairie_Users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_librairie_books1`
    FOREIGN KEY (`book_id`)
    REFERENCES `mydb`.`books` (`book_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
