<?php

namespace predocsTestCore;

/**
 * Interface para testes de requisições HTTP.
 */
interface testHttpInterface
{
    public function testRequestGet(): void;

    public function testRequestPost(): void;

    public function testRequestPut(): void;

    public function testRequestPatch(): void;

    public function testRequestDelete(): void;
}
