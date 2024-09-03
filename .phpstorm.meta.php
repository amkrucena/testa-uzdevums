<?php
// This is not a code, it is a source to make advanced type inference decisions

namespace PHPSTORM_META {
    use Psr\Container\ContainerInterface;

    override(ContainerInterface::get(0), type(0));
    override(\app(0), type(0));
}
