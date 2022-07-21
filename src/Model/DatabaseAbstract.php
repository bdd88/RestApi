<?php
namespace bdd88\RestApi\Model;

/** Abstract for creating database models. */
abstract class DatabaseAbstract
{
    public abstract function create():string;
    public abstract function read():string;
    public abstract function update():string;
    public abstract function delete():string;
}