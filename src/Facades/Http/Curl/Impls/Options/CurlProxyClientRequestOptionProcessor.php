<?php

namespace Magpie\Facades\Http\Curl\Impls\Options;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Http\Auths\ClientCertificateHttpAuthentication;
use Magpie\Facades\Http\Auths\TlsSrpHttpAuthentication;
use Magpie\Facades\Http\Auths\UsernamePasswordHttpAuthentication;
use Magpie\Facades\Http\Curl\CurlHttpClient;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\HttpAuthentication;
use Magpie\Facades\Http\Options\ProxyClientRequestOption;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionContext;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionProcessor;
use Magpie\General\Bitmask;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;

/**
 * ProxyClientRequestOption processor for CURL
 * @internal
 */
#[FeatureMatrixTypeClass(ProxyClientRequestOption::TYPECLASS, CurlHttpClient::TYPECLASS, HttpClientRequestOptionProcessor::class)]
class CurlProxyClientRequestOptionProcessor extends HttpClientRequestOptionProcessor
{
    /**
     * @inheritDoc
     */
    public function apply(HttpClientRequestOptionContext $context) : void
    {
        if (!$context instanceof CurlHttpClientRequestOptionContext) throw new UnexpectedException();
        if (!$this->option instanceof ProxyClientRequestOption) throw new UnexpectedException();

        [$proxyType, $proxyAddress] = static::decodeProxyRemote($this->option->remote, $this->option->options);

        $context->setOpt(CURLOPT_PROXYTYPE, $proxyType);
        $context->setOpt(CURLOPT_PROXY, $proxyAddress);

        if ($this->option->auth !== null) {
            $authOptions = static::decodeProxyAuthentication($context, $proxyType, $this->option->auth);
            foreach ($authOptions as $curlOption => $curlOptionValue) {
                $context->setOpt($curlOption, $curlOptionValue);
            }
        }
    }


    /**
     * Decode proxy remote address
     * @param string $remote
     * @param int $options
     * @return array
     * @throws UnsupportedValueException
     */
    protected static function decodeProxyRemote(string $remote, int $options) : array
    {
        $separatorPos = strpos($remote, '://');
        if ($separatorPos === false) {
            // Defaults to HTTP proxy
            return [CURLPROXY_HTTP, $remote];
        } else {
            return [static::acceptProxySchema(substr($remote, 0, $separatorPos), $options), substr($remote, $separatorPos + 3)];
        }
    }


    /**
     * Try to check proxy schema for valid type
     * @param string $schema
     * @param int $options
     * @return int
     * @throws UnsupportedValueException
     */
    protected static function acceptProxySchema(string $schema, int $options) : int
    {
        switch ($schema) {
            case 'http':
                if (Bitmask::isSet($options, ProxyClientRequestOption::OPT_DOWNGRADE_HTTP)) return CURLPROXY_HTTP_1_0;
                return CURLPROXY_HTTP;
            case 'https':
                return CURLPROXY_HTTPS;
            case 'socks4':
                return CURLPROXY_SOCKS4;
            case 'socks4a':
                return CURLPROXY_SOCKS4A;
            case 'socks5':
                if (Bitmask::isSet($options, ProxyClientRequestOption::OPT_FORWARD_DNS)) return CURLPROXY_SOCKS5_HOSTNAME;
                return CURLPROXY_SOCKS5;
            default:
                throw new UnsupportedValueException($schema, _l('proxy type'));
        }
    }


    /**
     * Decode proxy authentication
     * @param CurlHttpClientRequestOptionContext $context
     * @param int $proxyType
     * @param HttpAuthentication $auth
     * @return iterable<int, mixed>
     * @throws SafetyCommonException
     */
    protected static function decodeProxyAuthentication(CurlHttpClientRequestOptionContext $context, int $proxyType, HttpAuthentication $auth) : iterable
    {
        _used($proxyType);

        if ($proxyType === CURLPROXY_HTTPS) {
            // HTTPS proxy specific authentication settings
            if ($auth instanceof ClientCertificateHttpAuthentication) {
                yield from $context->translateCryptoContentOptions($auth->certificate,
                    CURLOPT_PROXY_SSLCERT,
                    CURLOPT_PROXY_SSLCERTTYPE,
                    null,
                );
                yield from $context->translateCryptoContentOptions($auth->privateKey,
                    CURLOPT_PROXY_SSLKEY,
                    CURLOPT_PROXY_SSLKEYTYPE,
                    CURLOPT_PROXY_KEYPASSWD,
                );
                return;
            } else if ($auth instanceof TlsSrpHttpAuthentication) {
                yield CURLOPT_PROXY_TLSAUTH_TYPE => 'SRP';
                yield CURLOPT_PROXY_TLSAUTH_USERNAME => $auth->credentials->username;
                yield CURLOPT_PROXY_TLSAUTH_PASSWORD => $auth->credentials->password;
            }
        }

        if ($auth instanceof UsernamePasswordHttpAuthentication) {
            yield CURLOPT_PROXYUSERPWD => $auth->credentials->username . ':' . $auth->credentials->password;
            return;
        }

        throw new UnsupportedValueException($auth, _l('proxy authentication'));
    }
}