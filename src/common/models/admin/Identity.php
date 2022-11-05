<?php

    namespace app\common\models\admin;

    use yii\web\IdentityInterface;


    /**
     * Class identity
     *
     * @property integer $id
     * @property string  $password
     * @property string  $auth_key
     * @property string  $created_at
     * @package app\common\models
     */
    abstract class Identity extends \app\common\models\Model implements IdentityInterface
    {
        public $passwordConfirm;

        /**
         * @param int $id
         *
         * @return Identity|null
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        public static function findIdentityByAccessToken($token, $type = null)
        {
            $clientId = $type == null ? '' : $type;

            return static::findByAccessToken($token, $clientId);
        }

        abstract public static function findByAccessToken(
            $accessToken,
            $clientId
        );

        abstract public function can($actionId);

        abstract public function generateAccessToken($clientId = '');

        /**
         * @return int
         */
        public function getId()
        {
            return $this->getPrimaryKey();
        }

        /**
         * @return string
         */
        public function getAuthKey()
        {
            return $this->auth_key;
        }

        /**
         * @param string $authKey
         *
         * @return bool
         */
        public function validateAuthKey($authKey)
        {
            return $this->auth_key = $authKey;
        }

        /**
         * @param $password
         *
         * @return bool
         */
        public function validatePassword($password)
        {
            return \Yii::$app->getSecurity()
                ->validatePassword($password, $this->password);
        }

        /**
         * @param bool $insert
         *
         * @return bool
         */
        public function beforeSave($insert)
        {
            if (parent::beforeSave($insert)) {
                if ($insert || !empty($this->passwordConfirm)) {
                    $this->setAuthKey();
                    $this->setPassword();
                } else {
                    unset($this->password);
                    unset($this->authKey);
                }
                return true;
            } else {
                return false;
            }
        }

        public function setAuthKey()
        {
            $this->auth_key = \Yii::$app->getSecurity()->generateRandomString();
        }

        public function setPassword()
        {
            $this->password = \Yii::$app->getSecurity()
                ->generatePasswordHash($this->password);
        }
    }