<?php
namespace frontend\controllers;

use common\exception\CantEatException;
use common\exception\IncorrectActionException;
use common\exception\IncorrectValueException;
use common\models\Fruit\Apple;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;

/**
 * Site controller
 */
class SiteController extends Controller
{

   const PER_SET = 20;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    private static function getApple($id) {
		if (null === $id) {
			Yii::$app->getSession()->addFlash('error', 'Id does not set');
			return false;
		}

		$apple = Apple::findOne($id);

		if (null === $apple) {
			Yii::$app->getSession()->addFlash('error', 'Object not found');
			return false;
		}

		return $apple;
	}

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
		$apples = Apple::getHaveNotEaten();

		return $this->render('apples', [
			'apples' => $apples,
		]);
    }

	/**
	 * @return \yii\web\Response
	 */
	public function actionDrop()
	{
		$request = Yii::$app->request;
		$id = $request->get('id');

		$apple = static::getApple($id);
		if (!$apple) {
			return $this->redirect(['index']);
		}

		try {
			$apple->fallToGround();
			$apple->save();
			Yii::$app->getSession()->addFlash('success', 'Apple successfully fell');
		} catch (IncorrectActionException $e) {
			Yii::$app->getSession()->addFlash('error', $e->getMessage());
		} finally {
			return $this->redirect(['index']);
		}

	}

	/**
	 * @return \yii\web\Response
	 */
	public function actionEat()
	{
		$request = Yii::$app->request;
		$id = $request->get('id');
		$apple = static::getApple($id);

		if (!$apple) {
			return $this->redirect(['index']);
		}
		try {
			if ($apple->load($request->post()) && $apple->save()) {
				Yii::$app->getSession()->addFlash('success', 'Apple successfully ate');
			}
		} catch (IncorrectValueException $e) {
			Yii::$app->getSession()->addFlash('error', $e->getMessage());
		} catch (CantEatException $e) {
			Yii::$app->getSession()->addFlash('error', $e->getMessage());
		} finally {
			return $this->redirect(['index']);
		}
	}

	/**
	 * @return \yii\web\Response
	 */
	public function actionGenerate() {
		Apple::deleteAll();
		for($i = 0; $i < static::PER_SET; $i++) {
			$apple = Apple::generateRandomApple();
			$apple->save();
		}

		Yii::$app->getSession()->addFlash('success', 'Apples successfully generated');
		return $this->redirect(['index']);
	}

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}
