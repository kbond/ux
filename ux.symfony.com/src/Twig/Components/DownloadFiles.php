<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use App\Service\DocumentStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
// #[AsTaggedItem('controller.service_arguments')]
final class DownloadFiles
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $year = 2025;

    public function __construct(
        private readonly DocumentStorage $documentStorage,
    ) {
    }

    #[LiveAction]
    public function download(): BinaryFileResponse
    {
        $file = $this->documentStorage->getFile('file.txt');

        return (new BinaryFileResponse($file))
            ->setContentDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                'file.txt'
            )
        ;
    }

    #[LiveAction]
    public function stream(): StreamedResponse
    {
        $file = $this->documentStorage->getFile('file.txt');

        return new StreamedResponse(
            function () use ($file) {
                $outputStream = fopen('php://output', 'wb');
                $inputStream = fopen($file, 'rb');

                stream_copy_to_stream($inputStream, $outputStream);

                fclose($outputStream);
                fclose($inputStream);
            },
            headers: [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    'file.txt'
                ),
            ]
        );
    }

    #[LiveAction]
    public function generate(#[LiveArg] string $format): BinaryFileResponse
    {
        $report = match ($format) {
            'csv' => $this->generateCsvReport($this->year),
            'json' => $this->generateJsonReport($this->year),
            'md' => $this->generateMarkdownReport($this->year),
            default => throw new \InvalidArgumentException('Invalid format provided'),
        };

        $file = new \SplTempFileObject();
        $file->fwrite($report);

        return new LiveDownloadResponse($file, 'report.'.$format);
    }

    private function generateCsvReport(int $year): string
    {
        $file = new \SplTempFileObject();
        // $file->fputcsv(['Month', 'Number', 'Name', 'Number of days']);
        foreach ($this->getReportData($year) as $row) {
            $file->fputcsv($row);
        }

        return $file->fread($file->ftell());
    }

    private function generateMarkdownReport(int $year): string
    {
        $rows = iterator_to_array($this->getReportData($year));

        foreach ($rows as $key => $row) {
            $rows[$key] = '|'.implode('|', $row).'|';
        }

        return implode("\n", $rows);
    }

    private function generateJsonReport(int $year): string
    {
        $rows = iterator_to_array($this->getReportData($year));

        return json_encode($rows, \JSON_FORCE_OBJECT | \JSON_THROW_ON_ERROR);
    }

    /**
     * @param int<2000,2025> $year The year to generate the report for (2000-2025)
     *
     * @return iterable<string, array{string, string}>
     */
    private function getReportData(int $year): iterable
    {
        foreach (range(1, 12) as $month) {
            $startDate = \DateTimeImmutable::createFromFormat('Y', $year)->setDate($year, $month, 1);
            $endDate = $startDate->modify('last day of this month');
            yield $month => [
                'name' => $startDate->format('F'),
                'month' => $startDate->format('F'),
                'number' => $startDate->format('Y-m'),
                'nb_days' => $endDate->diff($startDate)->days,
            ];
        }
    }
}
