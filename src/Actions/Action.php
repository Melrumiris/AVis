<?php

interface Action
{
    public function execute(?string $param): void;
}