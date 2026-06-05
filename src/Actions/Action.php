<?php

namespace src\Actions;
interface Action
{
    public function execute(?string $param): void;
}