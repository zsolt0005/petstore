<?php declare(strict_types=1);

namespace PetStore\Utils;

use Nette\Application\Response;
use Nette\Http\IRequest;
use PetStore\Data\JsonResponse;
use PetStore\Data\XmlResponse;

/**
 * Class ResponseUtils
 *
 * @package PetStore\Utils
 * @author  Zsolt Döme
 * @since   2024
 */
final class ResponseUtils
{
    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Creates a response data based on the initial requests accept header and the given data.
     *
     * @param IRequest $request
     * @param object $data
     *
     * @return Response
     */
    public static function mapDataToResponse(IRequest $request, object $data): Response
    {
        return match ($request->getHeader('accept'))
        {
            'application/xml' => new XmlResponse($data),
            default => new JsonResponse($data)
        };
    }
}