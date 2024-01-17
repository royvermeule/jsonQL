<?php

namespace bin\commands\utility;

use src\schema\SchemaBuilder;

interface Migration
{
    public function __invoke(SchemaBuilder $schema);
}