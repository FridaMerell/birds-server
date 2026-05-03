<?php

namespace App\Entity;

interface EditableEntityInterface{
    
    function isRestricted():bool;

    function getOwners():array;
    
}