<?php

namespace App\Controller;

use App\Entity\Sms;
use App\Util\CacheInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController
{
    private $cachePool;
    private $cacheUtil;
 
    public function __construct(
        MemcachedAdapter $cachePool,
        CacheInterface $cacheUtil
    ) {
        $this->cachePool = $cachePool;
        $this->cacheUtil = $cacheUtil;
    }

    /**
     * return reports
     *
     * @return array
     */
    public function report()
    {

        $allSmses = $this->allSmses();
        $apiUsage = $this->apiUsage();
        $apiError = $this->apiError();
        $tenPhones = $this->tenPhones();

        return $this->render(
            'report/report.html.twig',
            [
            'allSmses' => $allSmses,
            'apiUsage' => $apiUsage,
            'apiError' => $apiError,
            'tenPhones' => $tenPhones,
            ]
        );
    }

    /**
     * return all smses
     *
     * @return array
     */
    public function allSmses()
    {
        $data = $this->getItem("allSmses");
        if(isset($data))
        {
            $allSmses = $data["allSmses"];
        } else {
            $repository = $this->getDoctrine()->getRepository(Sms::class);
            $result = $repository->findBy(
                ['sendOrNot' => 1]
            );
            $allSmses = count($result);
            $this->saveItem("allSmses", array("allSmses" => $allSmses));
        }
        return $allSmses;
    }

    /**
     * return api Usage
     *
     * @return array
     */
    public function apiUsage()
    {
        $data = $this->getItem("apiUsage");
        if(isset($data))
        {
            $apiUsage = $data["apiUsage"];
        } else {
            $conn = $this->getDoctrine()->getManager()->getConnection();
            $sql = '
            SELECT COUNT(id) as count, api_url
            FROM api
            GROUP BY api_url;
                ';
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $apiUsage = $stmt->fetchAll();
            $this->saveItem("apiUsage", array("apiUsage" => $apiUsage));
        }
        return $apiUsage;
    }

    /**
     * return api Error
     *
     * @return array
     */
    public function apiError()
    {
        $data = $this->getItem("apiError");
        if(isset($data))
        {
            $apiError = $data["apiError"];
        } else {

        $conn = $this->getDoctrine()->getManager()->getConnection();
        $sql = '
        SELECT COUNT(id) as count, api_url
        FROM api
        GROUP BY api_url;
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $alldatas = $stmt->fetchAll();

        $sql = '
        SELECT COUNT(id) as count, api_url
        FROM api
        where send_or_not = 0
        GROUP BY api_url;
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $falsedatas = $stmt->fetchAll();

        $resultData = array();
        foreach($alldatas as $alldata)
        {
            $thereIs = 0;
            foreach($falsedatas as $falsedata)
            {
                if ($alldata['api_url'] == $falsedata['api_url']) {
                    $error = round(($falsedata['count'] / $alldata['count'])*100, 2);
                    $resultData[] = array("api_url" => $alldata["api_url"], "error" =>  $error);
                    $thereIs = 1;
                    break;
                }
            }
            if(!$thereIs) {
                $resultData[] = array("api_url" => $alldata["api_url"], "error" =>  0);
            }

        }
        $apiError = $resultData;

            $this->saveItem("apiError", array("apiError" => $apiError));
        }
        return $apiError;
    }

    /**
     * return ten Phones
     *
     * @return array
     */
    public function tenPhones()
    {
        $data = $this->getItem("tenPhones");
        if(isset($data))
        {
            $tenPhones = $data["tenPhones"];
        } else {
            $conn = $this->getDoctrine()->getManager()->getConnection();

            $sql = '
            SELECT COUNT(id) as count, phone
            FROM sms
            where send_or_not = 1
            GROUP BY phone
            ORDER BY COUNT(id) DESC
            LIMIT 10;
                ';
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $tenPhones = $stmt->fetchAll();

            $this->saveItem("tenPhones", array("tenPhones" => $tenPhones));
        }
        return $tenPhones;
    }

    /**
     * save item in cache system
     *
     * @return array
     */
    public function saveItem($key, $value)
    {
        return $this->cacheUtil->saveItem($this->cachePool, $key, $value);
    }
 
    /**
     * get item from cache system
     *
     * @return array
     */
    public function getItem($key)
    {
        return $this->cacheUtil->getItem($this->cachePool, $key);
    }
 
    /**
     * delete item from cache system
     *
     * @return void
     */
    public function deleteItem($key)
    {
        return $this->cacheUtil->deleteItem($this->cachePool, $key);
    }
 
    /**
     * delete cache system
     *
     * @return void
     */
    public function deleteAll()
    {
        return $this->cacheUtil->deleteAll($this->cachePool);
    }
}