<?php
namespace BlogAPI\Exceptions;

use Illuminate\Http\Response;

class UnauthorizedException extends \Exception
{
    protected $code;
    protected $message;
    protected $previous;

    public function __construct(string $message = "Unauthorized.", int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->code = $code;
        $this->message = $message;
        $this->previous = $previous;
    }

    public function render()
    {
        return response()->json([
            'error_code' => $this->code,
            'message' => $this->message,
        ], Response::HTTP_UNAUTHORIZED);
    }
}