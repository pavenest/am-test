<?php

namespace AMT\Container;


/**
 * Exception thrown when a class or a value is not found in the container.
 */
class BindingResolutionException extends \Exception  implements NotFoundExceptionInterface
{

}
