<?php namespace Milky\Exceptions\Transformers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is the model transformer class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class ModelTransformer implements TransformerInterface
{
    /**
     * Transform the provided exception.
     *
     * @param \Exception $exception
     *
     * @return \Exception
     */
    public function transform(Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            $exception = new NotFoundHttpException($exception->getMessage(), $exception, $exception->getCode());
        }

        return $exception;
    }
}
