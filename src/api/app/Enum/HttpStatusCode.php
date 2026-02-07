<?php

declare(strict_types=1);

namespace JR\Tracker\Enum;

use JR\Tracker\Exception\UndefinedCaseErrorException;

/**
 * @method int CONTINUE()
 * @method int SWITCHING_PROTOCOLS()
 * @method int PROCESSING()
 * @method int EARLY_HINTS()
 * @method int OK()
 * @method int CREATED()
 * @method int ACCEPTED()
 * @method int NON_AUTHORITATIVE_INFORMATION()
 * @method int NO_CONTENT()
 * @method int RESET_CONTENT()
 * @method int PARTIAL_CONTENT()
 * @method int MULTI_STATUS()
 * @method int ALREADY_REPORTED()
 * @method int THIS_IS_FINE()
 * @method int IM_USED()
 * @method int MULTIPLE_CHOICES()
 * @method int MOVED_PERMANENTLY()
 * @method int FOUND()
 * @method int SEE_OTHER()
 * @method int NOT_MODIFIED()
 * @method int USE_PROXY()
 * @method int TEMPORARY_REDIRECT()
 * @method int PERMANENT_REDIRECT()
 * @method int BAD_REQUEST()
 * @method int UNAUTHORIZED()
 * @method int PAYMENT_REQUIRED()
 * @method int FORBIDDEN()
 * @method int NOT_FOUND()
 * @method int METHOD_NOT_ALLOWED()
 * @method int NOT_ACCEPTABLE()
 * @method int PROXY_AUTHENTICATION_REQUIRED()
 * @method int REQUEST_TIMEOUT()
 * @method int CONFLICT()
 * @method int GONE()
 * @method int LENGTH_REQUIRED()
 * @method int PRECONDITION_FAILED()
 * @method int PAYLOAD_TOO_LARGE()
 * @method int URI_TOO_LONG()
 * @method int UNSUPPORTED_MEDIA_TYPE()
 * @method int RANGE_NOT_SATISFIABLE()
 * @method int EXPECTATION_FAILED()
 * @method int I_AM_A_TEAPOT()
 * @method int PAGE_EXPIRED()
 * @method int MISDIRECTED_REQUEST()
 * @method int UNPROCESSABLE_ENTITY()
 * @method int LOCKED()
 * @method int FAILED_DEPENDENCY()
 * @method int TOO_EARLY()
 * @method int UPGRADE_REQUIRED()
 * @method int PRECONDITION_REQUIRED()
 * @method int TOO_MANY_REQUESTS()
 * @method int REQUEST_HEADER_FIELDS_TOO_LARGE()
 * @method int LOGIN_TIME_OUT()
 * @method int NO_RESPONSE()
 * @method int RETRY_WITH()
 * @method int BLOCKED_BY_WINDOWS_PARENTAL_CONTROL()
 * @method int UNAVAILABLE_FOR_LEGAL_REASONS()
 * @method int CLIENT_CLOSED_THE_CONNECTION()
 * @method int X_FORWARDED_FOR_TOO_LARGE()
 * @method int REQUEST_HEADER_TOO_LARGE()
 * @method int SSL_CERTIFICATE_ERROR()
 * @method int SSL_CERTIFICATE_REQUIRED()
 * @method int HTTP_REQUEST_SENT_TO_HTTPS_PORT()
 * @method int INVALID_TOKEN()
 * @method int TOKEN_REQUIRED()
 * @method int INTERNAL_SERVER_ERROR()
 * @method int NOT_IMPLEMENTED()
 * @method int BAD_GATEWAY()
 * @method int SERVICE_UNAVAILABLE()
 * @method int GATEWAY_TIMEOUT()
 * @method int HTTP_VERSION_NOT_SUPPORTED()
 * @method int VARIANT_ALSO_NEGOTIATES()
 * @method int INSUFFICIENT_STORAGE()
 * @method int LOOP_DETECTED()
 * @method int BANDWIDTH_LIMIT_EXCEEDED()
 * @method int NOT_EXTENDED()
 * @method int NETWORK_AUTHENTICATION_REQUIRED()
 * @method int WEB_SERVER_RETURNED_AN_UNKNOWN_ERROR()
 * @method int WEB_SERVER_IS_DOWN()
 * @method int CONNECTION_TIMED_OUT()
 * @method int ORIGIN_IS_UNREACHABLE()
 * @method int A_TIMEOUT_OCCURRED()
 * @method int SSL_HANDSHAKE_FAILED()
 * @method int INVALID_SSL_CERTIFICATE()
 * @method int RAILGUN_ERROR()
 * @method int SITE_IS_OVERLOADED()
 * @method int SITE_IS_FROZEN()
 * @method int NETWORK_READ_TIMEOUT_ERROR()
 */
enum HttpStatusCode: int
{
    /**
     * Informational 1xx
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#1xx_informational_response 
     */
    case CONTINUING = 100; // `CONTINUE` is a reserved keyword
    case SWITCHING_PROTOCOLS = 101;
    case PROCESSING = 102;
    case EARLY_HINTS = 103;

    /**
     * Successful 2xx
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#2xx_success
     */
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT = 204;
    case RESET_CONTENT = 205;
    case PARTIAL_CONTENT = 206;
    case MULTI_STATUS = 207;
    case ALREADY_REPORTED = 208;
    case THIS_IS_FINE = 218;
    case IM_USED = 226;

    /**
     * Redirection 3xx
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_redirection
     */
    case MULTIPLE_CHOICES = 300;
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case SEE_OTHER = 303;
    case NOT_MODIFIED = 304;
    case USE_PROXY = 305;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;

    /**
     * Client Error 4xx
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#4xx_client_errors
     */
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case PAYMENT_REQUIRED = 402;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case NOT_ACCEPTABLE = 406;
    case PROXY_AUTHENTICATION_REQUIRED = 407;
    case REQUEST_TIMEOUT = 408;
    case CONFLICT = 409;
    case GONE = 410;
    case LENGTH_REQUIRED = 411;
    case PRECONDITION_FAILED = 412;
    case PAYLOAD_TOO_LARGE = 413;
    case URI_TOO_LONG = 414;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case RANGE_NOT_SATISFIABLE = 416;
    case EXPECTATION_FAILED = 417;
    case I_AM_A_TEAPOT = 418;
    case PAGE_EXPIRED = 419;
    case MISDIRECTED_REQUEST = 421;
    case UNPROCESSABLE_ENTITY = 422;
    case LOCKED = 423;
    case FAILED_DEPENDENCY = 424;
    case TOO_EARLY = 425;
    case UPGRADE_REQUIRED = 426;
    case PRECONDITION_REQUIRED = 428;
    case TOO_MANY_REQUESTS = 429;
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    case LOGIN_TIME_OUT = 440;
    case NO_RESPONSE = 444;
    case RETRY_WITH = 449;
    case BLOCKED_BY_WINDOWS_PARENTAL_CONTROL = 450;
    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    case CLIENT_CLOSED_THE_CONNECTION = 460;
    case X_FORWARDED_FOR_TOO_LARGE = 463;
    case REQUEST_HEADER_TOO_LARGE = 494;
    case SSL_CERTIFICATE_ERROR = 495;
    case SSL_CERTIFICATE_REQUIRED = 496;
    case HTTP_REQUEST_SENT_TO_HTTPS_PORT = 497;
    case INVALID_TOKEN = 498;
    case TOKEN_REQUIRED = 499;

    /**
     * Server Error 5xx
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#5xx_server_errors
     */
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
    case HTTP_VERSION_NOT_SUPPORTED = 505;
    case VARIANT_ALSO_NEGOTIATES = 506;
    case INSUFFICIENT_STORAGE = 507;
    case LOOP_DETECTED = 508;
    case BANDWIDTH_LIMIT_EXCEEDED = 509;
    case NOT_EXTENDED = 510;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;
    case WEB_SERVER_RETURNED_AN_UNKNOWN_ERROR = 520;
    case WEB_SERVER_IS_DOWN = 521;
    case CONNECTION_TIMED_OUT = 522;
    case ORIGIN_IS_UNREACHABLE = 523;
    case A_TIMEOUT_OCCURRED = 524;
    case SSL_HANDSHAKE_FAILED = 525;
    case INVALID_SSL_CERTIFICATE = 526;
    case RAILGUN_ERROR = 527;
    case SITE_IS_OVERLOADED = 529;
    case SITE_IS_FROZEN = 530;
    case NETWORK_READ_TIMEOUT_ERROR = 598;

    /**
     * @return int
     */
    public function __invoke(): int
    {
        return $this->value;
    }

    /**
     * @param  string  $name
     * @param  mixed  $args
     * @return int
     *
     * @throws UndefinedCaseErrorException
     */
    public static function __callStatic(string $name, mixed $args): int
    {
        $cases = HttpStatusCode::cases();

        foreach ($cases as $case) {
            if ($case->name === $name) {
                return $case->value;
            }
        }

        throw new UndefinedCaseErrorException(
            enum: HttpStatusCode::class,
            case: $name,
        );
    }

    /**
     * @return array
     */
    public static function options(): array
    {
        return array_column(HttpStatusCode::cases(), 'value', 'name');
    }

    /**
     * @return array
     */
    public static function names(): array
    {
        return array_column(HttpStatusCode::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(HttpStatusCode::cases(), 'value');
    }
}