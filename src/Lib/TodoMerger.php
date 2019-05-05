<?php

namespace APPointer\Lib;

use APPointer\Lib\DI;
use APPointer\Lib\MediaCenter;
use Doctrine\ORM\EntityManagerInterface;
use APPointer\RemoteEntity\Syncronisation;
use APPointer\RemoteEntity\RemoteTodo;
use APPointer\Entity\Todo;

/**
 * @class TodoMerger
 *
 * Updates one todo table with another.
 */
class TodoMerger {

    /** @var EntityManagerInterface $localEm */
    private $localEm;

    /** @var EntityManagerInterface $remoteEm */
    private $remoteEm;

    public function __construct(EntityManagerInterface $localEm, EntityManagerInterface $remoteEm)
    {
        $this->localEm  = $localEm;
        $this->remoteEm = $remoteEm;
    }

    public function mergeLocalToRemote()
    {
        $newSync = Syncronisation::createWithSourceTarget(`hostname`, 'repository');
        $this->remoteEm->persist($newSync);

        // TODO SNI: Das Repo irgendwie injecten, damit können wir auch besser Testen 
        $lastSync = $this->remoteEm->getRepository(Syncronisation::class)
            ->findOneBy(['source' => trim(`hostname`)], ['time' => 'DESC']);
        $lastSyncDate = $lastSync ? $lastSync->getTime() : new \DateTime('2000-01-01 00:00:00');

        // TODO SNI: Das auch kapseln in eine Repo-Funktion, schon zum Testen
        $localTodos = $this->localEm
            ->createQuery('SELECT t FROM APPointer\Entity\Todo t INDEX BY t.localId WHERE t.globalId is NULL OR t.updatedAt > :lastSyncDate')
            ->setParameter('lastSyncDate', $lastSyncDate)
            ->getResult();

        // TODO SNI: Kapseln in Repo-Funktion, auch zum Testen
        $newRemoteTodos = [];
        $oldRemoteTodos = $this->remoteEm
            ->createQuery('SELECT t FROM APPointer\RemoteEntity\RemoteTodo t INDEX BY t.globalId WHERE t.globalId IN (:globalIds)')
            ->setParameter('globalIds', array_filter(array_map(function($localTodo) {
                return $localTodo->getGlobalId();
            }, $localTodos)))
            ->getResult();

        foreach ($localTodos as $localTodo) {
            $globId = $localTodo->getGlobalId();

            // If there is a $globId, $oldRemoteTodos[$globId] has to be set.
            if ($globId && isset($oldRemoteTodos[$globId])) {
                $oldRemoteTodo = $oldRemoteTodos[$globId];
                Todo::setArrayValues($oldRemoteTodo, $localTodo->getArrayRepresentation());
                $oldRemoteTodo
                    ->setLastSyncTime($newSync->getTime())
                    ->setLastSyncSource(trim(`hostname`))
                    ;
            } else {
                $newRemoteTodo = RemoteTodo::createFromArray($localTodo->getArrayRepresentation());
                $this->remoteEm->persist($newRemoteTodo);
                $newRemoteTodo
                    ->setLastSyncTime($newSync->getTime())
                    ->setLastSyncSource(trim(`hostname`))
                    ;
                $newRemoteTodos[$localTodo->getLocalId()] = $newRemoteTodo;
            }
        }

        $this->remoteEm->flush();

        // Write global ids to local db
        foreach ($newRemoteTodos as $localId => $newRemoteTodo) {
            $localTodos[$localId]->setGlobalId($newRemoteTodo->getGlobalId());
        }
        $this->localEm->flush();
    }

    public function mergeRemoteToLocal()
    {
        $newSync = Syncronisation::createWithSourceTarget(`hostname`, 'repository');
        $this->remoteEm->persist($newSync);

        $lastDownload = $this->remoteEm->getRepository(Syncronisation::class)
            ->findOneBy(['target' => trim(`hostname`)], ['time' => 'DESC']);
        $lastDownloadTime = $lastDownload ? $lastDownload->getTime() : new \DateTime('2000-01-01 00:00:00');

        $remoteTodos = $this->remoteEm
            ->createQuery('SELECT r FROM APPointer\RemoteEntity\RemoteTodo r INDEX BY r.globalId
                WHERE r.lastSyncSource <> :hostname AND r.lastSyncTime > :lastDownloadTime')
            ->setParameter('hostname', trim(`hostname`))
            ->setParameter('lastDownloadTime', $lastDownloadTime)
            ->getResult();

        $newLocalTodos = [];
        $oldLocalTodos = $this->localEm
            ->createQuery('SELECT l FROM APPointer\Entity\Todo l INDEX BY l.globalId WHERE l.globalId IN (:globalIds)')
            ->setParameter('globalIds', array_filter(array_map(function($remoteTodo) {
                return $remoteTodo->getGlobalId();
            }, $remoteTodos)))
            ->getResult();

        foreach ($remoteTodos as $remoteTodo) {
            $globId = $remoteTodo->getGlobalId();

            // If there is a $globId, $oldRemoteTodos[$globId] has to be set.
            if ($globId && isset($oldLocalTodos[$globId])) {
                $oldLocalTodo = $oldLocalTodos[$globId];
                Todo::setArrayValues($oldLocalTodo, $remoteTodo->getArrayRepresentation());
            } else {
                $newLocalTodo = Todo::createFromArray($remoteTodo->getArrayRepresentation());
                $this->localEm->persist($newLocalTodo);
                $newLocalTodos[$globId] = $newLocalTodo;
            }
        }

        $this->localEm->flush();
        $this->localEm->flush();
    }
}
