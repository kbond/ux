<?php

namespace Symfony\UX\LiveComponent;

use SplFileInfo;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
final class LiveDownloadResponse extends BinaryFileResponse
{
    public const HEADER_LIVE_DOWNLOAD = 'X-Live-Download';

    public function __construct(string|SplFileInfo $file, ?string $filename = null)
    {
        if (\is_string($file)) {
            $file = new SplFileInfo($file);
        }

        if ((!$file instanceof SplFileInfo)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $file));
        }

        if ($file instanceof SplTempFileObject) {
            $file->rewind();
        }

        parent::__construct($file, 200, [
            self::HEADER_LIVE_DOWNLOAD => 1,
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename ?? basename($file)),
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => $file instanceof SplTempFileObject ? 0 : $file->getSize(),
        ], false, HeaderUtils::DISPOSITION_ATTACHMENT);
    }
}
