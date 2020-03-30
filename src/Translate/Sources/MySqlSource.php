<?php

namespace ALI\Translation\Translate\Sources;

use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use PDO;
use ALI\Translation\Languages\LanguageInterface;
use ALI\Translation\Translate\Sources\Exceptions\MySqlSource\LanguageNotExistsException;

/**
 * Class MySqlSource
 * @package ALI\Translation\Sources
 */
class MySqlSource implements SourceInterface
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    private $originalLanguageAlias;

    /**
     * @var string
     */
    protected $originalTableName;

    /**
     * @var string
     */
    protected $translateTableName;

    /**
     * @param PDO $pdo
     * @param string $originalLanguageAlias
     * @param string $originalTableName
     * @param string $translateTableName
     */
    public function __construct(
        PDO $pdo,
        $originalLanguageAlias,
        $originalTableName = 'ali_original',
        $translateTableName = 'ali_translate'
    )
    {
        $this->pdo = $pdo;
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->originalTableName = $originalTableName;
        $this->translateTableName = $translateTableName;
    }

    /**
     * @return string
     */
    public function getOriginalLanguageAlias()
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @param string $phrase
     * @param string $languageAliasAlias
     * @return string
     * @throws SourceException
     */
    public function getTranslate($phrase, $languageAliasAlias)
    {
        $translates = $this->getTranslates([$phrase], $languageAliasAlias);
        if ($translates) {
            return current($translates);
        }

        throw new SourceException('Empty list of translated phrases');
    }

    /**
     * @param string $languageAlias
     * @param LanguageInterface $language
     * @return array
     */
    public function getTranslates(array $phrases, $languageAlias)
    {
        if ($languageAlias === $this->originalLanguageAlias) {
            // TODO check if it's correct response
            return array_combine($phrases, $phrases);
        }
        if (!$phrases) {
            return [];
        }

        list($whereQuery, $valuesForWhereBinding) = $this->prepareParamsForQuery($phrases, 'select');

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content_index`, o.`content` as `original`, t.`content` as `translate`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
                LEFT JOIN `' . $this->translateTableName . '` AS `t` ON (`o`.`id`=`t`.`original_id` AND `t`.`language_alias`=:languageAlias)
            WHERE ' . implode(' OR ', $whereQuery) . '
            LIMIT ' . count($phrases)
        );
        $dataQuery->bindValue('languageAlias', $languageAlias, PDO::PARAM_STR);

        $this->bindParams($valuesForWhereBinding, $dataQuery);

        $dataQuery->execute();

        $translates = [];
        while ($translateRow = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            $translates[$translateRow['original']] = $translateRow['translate'];
        }

        //phrases that aren't in the database
        foreach ($phrases as $phrase) {
            if (!array_key_exists($phrase, $translates)) {
                $translates[$phrase] = '';
            }
        }

        return $translates;
    }

    /**
     * Generate keys for find original phrase in database
     * @param string $phrase
     * @return array
     */
    protected function createOriginalQueryParams($phrase)
    {
        $contentIndex = mb_substr($phrase, 0, 64, 'utf8');

        return [
            'contentIndex' => $contentIndex,
            'content' => $phrase,
        ];
    }

    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     * @throws LanguageNotExistsException
     */
    public function saveTranslate($languageAlias, $original, $translate)
    {
        $originalId = $this->getOriginalId($original);
        if (!$originalId) {
            $originalId = $this->insertOriginal($original);
        }

        $this->saveTranslateByOriginalId($languageAlias, $originalId, $translate);
    }

    /**
     * @param string $original
     * @return mixed
     */
    public function getOriginalId($original)
    {
        $statement = $this->pdo->prepare('
                SELECT id FROM `' . $this->originalTableName . '` WHERE content_index=:contentIndex AND content=:content
            ');
        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }
        $statement->execute();
        $originalId = $statement->fetch(PDO::FETCH_COLUMN);

        return $originalId;
    }

    /**
     * @param string $original
     * @return string
     */
    public function insertOriginal($original)
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->originalTableName . '` (`content_index`, `content`) VALUES (:contentIndex, :content)'
        );

        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }

        $statement->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $languageAlias
     * @param int $originalId
     * @param string $translate
     * @throws LanguageNotExistsException
     */
    protected function saveTranslateByOriginalId($languageAlias, $originalId, $translate)
    {
        $updatePdo = $this->pdo->prepare('
                INSERT INTO `' . $this->translateTableName . '` (`original_id`, `language_alias`, `content`)
                VALUES (:id, :languageAlias, :content)
                ON DUPLICATE KEY UPDATE `content`=:content
            ');
        $updatePdo->bindParam(':content', $translate, PDO::PARAM_STR);
        $updatePdo->bindParam(':id', $originalId, PDO::PARAM_INT);
        $updatePdo->bindParam(':languageAlias', $languageAlias, PDO::PARAM_STR);
        $updatePdo->execute();
    }

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete($original)
    {
        $statement = $this->pdo->prepare('
                DELETE FROM `' . $this->originalTableName . '` WHERE content_index=:contentIndex AND content=:content
            ');
        $queryParams = $this->createOriginalQueryParams($original);
        foreach ($queryParams as $queryKey => $queryParam) {
            $statement->bindValue($queryKey, $queryParam);
        }
        $statement->execute();
    }

    /**
     * @param string[] $phrases
     */
    public function saveOriginals(array $phrases)
    {
        if (!$phrases) {
            return;
        }
        $phrasesForInsert = array_diff($phrases, $this->getExistOriginals($phrases));
        if (!$phrasesForInsert) {
            return;
        }

        list($valuesQuery, $valuesForWhereBinding) = $this->prepareParamsForQuery($phrases, 'insert');

        $statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->originalTableName . '`
                        (`content_index`, `content`)
                            VALUES ' . implode(',', $valuesQuery) . '
                            '
        );

        $this->bindParams($valuesForWhereBinding,$statement);

        $statement->execute();
    }

    /**
     * @param array $phrases
     * @return array|mixed|string[]
     */
    public function getExistOriginals(array $phrases)
    {
        if (!$phrases) {
            return [];
        }

        list($whereQuery, $valuesForWhereBinding) = $this->prepareParamsForQuery($phrases, 'select');

        $dataQuery = $this->pdo->prepare(
            'SELECT o.`id`, o.`content_index`, o.`content` as `original`
                FROM `' . $this->originalTableName . '` AS `o`
                FORCE INDEX(indexContentIndex)
            WHERE ' . implode(' OR ', $whereQuery) . '
            LIMIT ' . count($phrases)
        );
        $this->bindParams($valuesForWhereBinding, $dataQuery);

        $dataQuery->execute();

        $existPhrases = [];
        while ($existPhrase = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
            $existPhrases[] = $existPhrase['original'];
        }

        return $existPhrases;
    }

    /**
     * @param array $phrases
     * @param string $type
     * @return array
     */
    private function prepareParamsForQuery(array $phrases, $type)
    {
        $queryParts = [];
        $valuesForWhereBinding = [];
        $queryIndexIncrement = 1;
        foreach ($phrases as $keyForBinding => $phrase) {
            $queryIndexIncrement++;
            $contentIndexKey = 'content_index_' . $queryIndexIncrement;
            $queryIndexIncrement++;
            $contentKey = 'content_' . $queryIndexIncrement;
            $valuesForWhereBinding[$keyForBinding] = [
                'phrase' => $phrase,
                'contentIndexKey' => $contentIndexKey,
                'contentKey' => $contentKey,
            ];
            switch ($type){
                case 'select':
                    $queryParts[$keyForBinding] = '(o.`content_index`=:' . $contentIndexKey . ' AND BINARY o.`content`=:' . $contentKey . ')';
                    break;
                case 'insert':
                    $queryParts[$keyForBinding] = '(:' . $contentIndexKey . ', :' . $contentKey . ')';
                    break;
                default:
                    throw new \Exception('Invalid type');
                    break;
            }
        }

        return [$queryParts, $valuesForWhereBinding];
    }

    /**
     * @param $valuesForWhereBinding
     * @param \PDOStatement $dataQuery
     */
    private function bindParams($valuesForWhereBinding, \PDOStatement $dataQuery)
    {
        foreach ($valuesForWhereBinding as $dataForBinding) {
            $originalQueryParams = $this->createOriginalQueryParams($dataForBinding['phrase']);

            $contentIndexKey = $dataForBinding['contentIndexKey'];
            $contentIndex = $originalQueryParams['contentIndex'];
            $contentKey = $dataForBinding['contentKey'];
            $content = $originalQueryParams['content'];

            $dataQuery->bindValue($contentIndexKey, $contentIndex, PDO::PARAM_STR);
            $dataQuery->bindValue($contentKey, $content, PDO::PARAM_STR);
        }
    }
}
