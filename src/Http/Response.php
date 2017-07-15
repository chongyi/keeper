<?php
/**
 * Response.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Dybasedev\Keeper\Http\Exceptions\InvalidSwooleResponseException;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    /**
     * @var SwooleResponse
     */
    protected $swooleResponse;

    /**
     * @param SwooleResponse $swooleResponse
     *
     * @return Response
     */
    public function setSwooleResponse(SwooleResponse $swooleResponse)
    {
        $this->swooleResponse = $swooleResponse;

        return $this;
    }

    /**
     * @return SwooleResponse
     */
    public function getSwooleResponse()
    {
        if (!$this->swooleResponse instanceof \Swoole\Http\Response) {
            throw new InvalidSwooleResponseException();
        }

        return $this->swooleResponse;
    }

    /**
     * @inheritDoc
     */
    public function sendHeaders()
    {
        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }

        // headers
        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                $this->getSwooleResponse()->header($name, $value);
            }
        }

        // status
        $this->getSwooleResponse()->status($this->statusCode);

        // cookies
        /** @var Cookie $cookie */
        foreach ($this->headers->getCookies() as $cookie) {
            $this->swooleResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(),
                $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sendContent()
    {
        $this->getSwooleResponse()->end($this->content);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        return $this;
    }
}