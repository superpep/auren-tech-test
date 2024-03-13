<?php
namespace App\Service;

use phpDocumentor\Reflection\Types\Void_;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Country;

class CountryImportService
{

    public function __construct(
        private HttpClientInterface $httpClient, 
        private EntityManagerInterface $entityManager)
    {
    }

    public function importCountries(): void
    {
        $response = $this->httpClient->request('GET', $_ENV['COUNTRIES_API'] . '/all');
        $countriesData = $response->toArray();

        foreach ($countriesData as $countryData) {
            $existingCountry = $this->entityManager->getRepository(Country::class)->findOneByName(
                $countryData['name']['common']
            );

            if (!$existingCountry) { // Si el país no existe, lo introducimos a la base de datos
                $country = new Country();
                $country->setName($countryData['name']['common']);
                $country->setArea($countryData['area'] ?? null);
                $country->setIndependent(isset($countryData['independent']) ? $countryData['independent'] : false);
                $country->setCapital($countryData['capital'][0] ?? null);

                $this->entityManager->persist($country);
            } else { // Si existe, comprobamos si la información es correcta.
                if (
                    $existingCountry->getArea() !== ($countryData['area'] ?? null) ||
                    $existingCountry->isIndependent() !== (isset($countryData['independent']) ? $countryData['independent'] : false) ||
                    $existingCountry->getCapital() !== ($countryData['capital'][0] ?? null)
                ) {
                    $existingCountry->setArea($countryData['area'] ?? null);
                    $existingCountry->setIndependent(isset($countryData['independent']) ? $countryData['independent'] : false);
                    $existingCountry->setCapital($countryData['capital'][0] ?? null);

                    $this->entityManager->persist($existingCountry);
                }
            }
        }

        $this->entityManager->flush();
    }

    public function importCountry(Country $country): void
    {
        $response = $this->httpClient->request('GET', $_ENV['COUNTRIES_API'] . '/name/' . $country->getName());
        $countryData = $response->toArray();
        if (
            $country->getArea() !== ($countryData[0]['area'] ?? null) ||
            $country->isIndependent() !== (isset($countryData[0]['independent']) ? $countryData[0]['independent'] : false) ||
            $country->getCapital() !== ($countryData[0]['capital'][0] ?? null)
        ) {
            $country->setArea($countryData[0]['area'] ?? null);
            $country->setIndependent(isset($countryData[0]['independent']) ? $countryData[0]['independent'] : false);
            $country->setCapital($countryData[0]['capital'][0] ?? null);

            $this->entityManager->persist($country);
        }
        $this->entityManager->flush();
    }
}
