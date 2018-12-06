<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Quanta\DI\Arguments\Argument;
use Quanta\DI\Arguments\Placeholder;
use Quanta\DI\Arguments\VariadicArgument;
use Quanta\DI\Parameters\ParameterInterface;
use Quanta\DI\Arguments\Pools\NameAliasMap;
use Quanta\DI\Arguments\Pools\ArgumentPoolInterface;

describe('NameAliasMap', function () {

    beforeEach(function () {

        $this->pool = new NameAliasMap([
            'name1' => SomeClass::class,
        ]);

    });

    it('should implement ArgumentPoolInterface', function () {

        expect($this->pool)->toBeAnInstanceOf(ArgumentPoolInterface::class);

    });

    describe('->argument()', function () {

        beforeEach(function () {

            $this->container = mock(ContainerInterface::class);
            $this->parameter = mock(ParameterInterface::class);

        });

        context('when the given parameter name is in the map', function () {

            beforeEach(function () {

                $this->parameter->name->returns('name1');

            });

            context('when the given parameter is not variadic', function () {

                beforeEach(function () {

                    $this->parameter->isVariadic->returns(false);

                });

                context('when the container entry associated to the parameter is not an array', function () {

                    it('should return an Argument containing the container entry', function () {

                        $instance = new class {};

                        $this->container->get->with(SomeClass::class)->returns($instance);

                        $test = $this->pool->argument($this->container->get(), $this->parameter->get());

                        expect($test)->toEqual(new Argument($instance));

                    });

                });

                context('when the container entry associated to the parameter is an array', function () {

                    it('should return an Argument containing the container entry', function () {

                        $instances = [new class {}, new class {}, new class {}];

                        $this->container->get->with(SomeClass::class)->returns($instances);

                        $test = $this->pool->argument($this->container->get(), $this->parameter->get());

                        expect($test)->toEqual(new Argument($instances));

                    });

                });

            });

            context('when the given parameter is variadic', function () {

                beforeEach(function () {

                    $this->parameter->isVariadic->returns(true);

                });

                context('when the container entry associated to the parameter is an array', function () {

                    it('should return a VariadicArgument containing the container entry', function () {

                        $instances = [new class {}, new class {}, new class {}];

                        $this->container->get->with(SomeClass::class)->returns($instances);

                        $test = $this->pool->argument($this->container->get(), $this->parameter->get());

                        expect($test)->toEqual(new VariadicArgument($instances));

                    });

                });

                context('when the container entry associated to the parameter is not an array', function () {

                    it('it should throw a logic exception', function () {

                        $this->container->get->with(SomeClass::class)->returns('value');

                        $test = function () {
                            $this->pool->argument($this->container->get(), $this->parameter->get());
                        };

                        expect($test)->toThrow(new LogicException);

                    });

                });

            });

        });

        context('when the given parameter name is not in the map', function () {

            it('should return a placeholder', function () {

                $this->parameter->name->returns('name3');

                $test = $this->pool->argument($this->container->get(), $this->parameter->get());

                expect($test)->toEqual(new Placeholder);

            });

        });

    });

});