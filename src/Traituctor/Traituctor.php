<?php namespace Nabeghe\Traituctor;

use Nabeghe\Reflecty\Reflecty;

class Traituctor
{
    /**
     * Calls the pseudo-constructor method for all traits used in an object.<br>
     * This method is best called within the class constructor.<br>
     *
     * @param  object  $object  The object.
     * @param  array  $args  Arguments that will be sent variadic to the constructor method in traits. Default an empty array.
     * @param  bool  $checkreqs  Should prerequisite traits be checked?
     * @param  string  $prefix  The prefix of the constructor method name in traits, followed by the name of the trait itself. Defalt `__construct`.
     * @return bool True if a constructor runs; false otherwise.
     */
    public static function construct(
        object $object,
        $args = null,
        bool $checkreqs = false,
        string $prefix = '__construct'
    ): bool {
        if (!is_array($args)) {
            if (is_null($args)) {
                $args = [];
            } else {
                $args = [$args];
            }
        }

        $traits = Reflecty::classUsesRecursive($object);
        if (!$traits) {
            return false;
        }
        //$traits = array_map(function ($trait) {
        //    return basename(str_replace('\\', '/', $trait));
        //}, $traits);

        $ok = false;

        if (!$checkreqs) {
            foreach ($traits as $trait) {
                $trait_short_name = basename(str_replace('\\', '/', $trait));
                $trait_special_constructor = $prefix.$trait_short_name;
                if (method_exists($object, $trait_special_constructor)) {
                    $object->$trait_special_constructor(...$args);
                    $ok = true;
                }
            }
        } else {
            $executed_traits = [];

            $execute_constructor_with_dependencies = function ($trait, $isreq = false) use (
                &$traits,
                &$object,
                &$args,
                &$prefix,
                &$execute_constructor_with_dependencies,
                &$executed_traits,
                &$ok
            ) {
                if (in_array($trait, $executed_traits)) {
                    return;
                }

                $trait_short_name = basename(str_replace('\\', '/', $trait));
                $trait_special_constructor = $prefix.$trait_short_name;

                if ($isreq && !in_array($trait, $traits)) {
                    throw new \RuntimeException("Trait `$trait` Requirements are not provided in the class `".get_class($object)."`.");
                }

                if (!method_exists($object, $trait_special_constructor)) {
                    return;
                }

                if (PHP_VERSION_ID >= 8000 && $requirements = Requirements::detect($trait)) {
                    foreach ($requirements->requirements as $required_trait) {
                        if (in_array($required_trait, class_uses($object))) {
                            $execute_constructor_with_dependencies($required_trait, true);
                        }
                    }
                }

                $object->$trait_special_constructor(...$args);
                $executed_traits[] = $trait;
                $ok = true;
            };

            foreach ($traits as $trait) {
                $execute_constructor_with_dependencies($trait);
                $ok = true;
            }
        }

        return $ok;
    }
}