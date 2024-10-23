<?php namespace Nabeghe\Traituctor;

#[\Attribute]
class Requirements
{
    public array $requirements;

    public function __construct(...$data)
    {
        $this->requirements = $data;
    }

    public static function detect($class): ?static
    {
        if (PHP_VERSION_ID <= 8000) {
            return null;
        }

        try {
            $r = new \ReflectionClass($class);
            $attributes = $r->getAttributes(static::class);
            if ($attributes) {
                /**
                 * @var self $requirements
                 */
                $requirements = $attributes[0]->newInstance();
                return $requirements;
            }
        } catch (\ReflectionException $e) {
        }

        return null;
    }
}