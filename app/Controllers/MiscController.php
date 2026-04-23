<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class MiscController extends Controller
{
    public function prospectus(): void
    {
        $this->requireLogin();
        $user = User::findById($this->session->userId());
        $this->render('pages/prospectus', compact('user'));
    }

    public function resultLookup(): void
    {
        $this->requireLogin();
        $user = User::findById($this->session->userId());
        $this->render('pages/result-lookup', compact('user'));
    }

    public function lookupOfficialResult(): void
    {
        $this->requireLogin();

        $body = json_decode(file_get_contents('php://input'), true);
        $year = $body['year'] ?? '';
        $term = $body['term'] ?? '';
        $programme = $body['programme'] ?? '';
        $batch = $body['batch'] ?? '';
        $examType = $body['examType'] ?? '';
        $code = trim($body['code'] ?? '');

        if ($year === '' || $term === '' || $code === '') {
            $this->json(['error' => 'Missing required fields: year, term, and student ID.'], 400);
        }

        $muBaseUrl = 'https://metrouni.edu.bd/sites/department-of-software-engineering/result-se';
        $postData = http_build_query([
            'academic_year' => $year,
            'term' => $term,
            'programme' => $programme,
            'batch' => $batch,
            'exam_type' => $examType,
            'code' => $code,
        ]);

        $ch = curl_init($muBaseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: Mozilla/5.0 (compatible; EduSync/1.0)',
                'Referer: https://metrouni.edu.bd/',
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $json = json_decode($response, true);
            if (is_array($json)) {
                $this->json($json);
            }

            $dom = new \DOMDocument();
            @$dom->loadHTML($response);
            $xpath = new \DOMXPath($dom);

            $rows = $xpath->query('//table[contains(@class,"result") or contains(@id,"result")]//tr');
            $results = [];
            $name = '';

            $nameNodes = $xpath->query('//*[contains(@class,"student-name") or contains(@id,"student-name")]');
            if ($nameNodes->length > 0) {
                $name = trim($nameNodes->item(0)->textContent);
            }

            if ($rows && $rows->length > 1) {
                foreach ($rows as $index => $row) {
                    if ($index === 0) {
                        continue;
                    }

                    $cells = $row->getElementsByTagName('td');
                    if ($cells->length >= 4) {
                        $results[] = [
                            'code' => trim($cells->item(0)->textContent ?? ''),
                            'title' => trim($cells->item(1)->textContent ?? ''),
                            'credit' => trim($cells->item(2)->textContent ?? ''),
                            'marks' => trim($cells->item(3)->textContent ?? ''),
                            'grade' => trim($cells->item(4)->textContent ?? ''),
                            'gp' => (float) trim($cells->item(5)->textContent ?? '0'),
                            'status' => trim($cells->item(6)->textContent ?? ''),
                        ];
                    }
                }

                if (!empty($results)) {
                    $this->json([
                        'name' => $name,
                        'id' => $code,
                        'programme' => $programme,
                        'batch' => $batch,
                        'results' => $results,
                    ]);
                }
            }
        }

        $errorMsg = 'Result not found on the MU server. ';
        if ($httpCode === 0) {
            $errorMsg = 'Could not connect to the MU result server. Please visit metrouni.edu.bd directly.';
        } elseif ($httpCode === 404) {
            $errorMsg = 'The MU result page could not be found. It may have moved.';
        } elseif ($httpCode === 403 || $httpCode === 429) {
            $errorMsg = 'Access to MU result server was blocked. Please visit metrouni.edu.bd directly.';
        } else {
            $errorMsg .= "Please double-check your details and try the official MU website at metrouni.edu.bd. (HTTP $httpCode)";
        }

        $this->json(['error' => $errorMsg], 502);
    }
}
