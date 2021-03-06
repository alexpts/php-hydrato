<?php
declare(strict_types=1);

use Faker\Generator;
use PHPUnit\Framework\TestCase;
use PTS\Hydrator\ExtractClosure;
use PTS\Hydrator\Extractor;
use PTS\Hydrator\ExtractorInterface;
use PTS\Hydrator\HydrateClosure;
use PTS\Hydrator\Hydrator;
use PTS\Hydrator\HydratorInterface;
use PTS\Hydrator\HydratorService;
use PTS\Hydrator\Normalizer;
use PTS\Hydrator\UserModel;

require_once __DIR__ . '/data/UserModel.php';

class HydratorServiceTest extends TestCase
{
    protected HydratorService $hydrator;
    protected Generator $faker;
    protected Normalizer $normalizer;

    public function setUp(): void
    {
        $hydrator = new Hydrator(new HydrateClosure);
        $extractor = new Extractor(new ExtractClosure);

        $this->hydrator = new HydratorService($extractor, $hydrator);
        $this->faker = Faker\Factory::create();
        $this->normalizer = new Normalizer;
    }

	/**
	 * @return array
	 *
	 * @throws Exception
	 */
	protected function createUserDto(): array
    {
        return [
            'id' => random_int(1, 9999),
            'creAt' => new \DateTime($this->faker->date),
            'name' => $this->faker->name,
            'login' => $this->faker->name,
            'active' => $this->faker->randomElement([true, false]),
            'email' => $this->faker->email,
        ];
    }

	/**
	 * @throws Exception
	 */
	public function testHydrate(): void
    {
        $userDto = $this->createUserDto();
        $rules = [
            'id' => [],
            'creAt' => [],
            'name' => [],
            'login' => [],
            'active' => [],
            'email' => [],
        ];
        $rules = $this->normalizer->normalize($rules);

        /** @var UserModel $model */
        $model = $this->hydrator->hydrate($userDto, UserModel::class, $rules);

        self::assertInstanceOf(UserModel::class, $model);
        self::assertEquals($userDto['creAt'], $model->getCreAt());
        self::assertEquals($userDto['email'], $model->getEmail());
        self::assertEquals($userDto['name'], $model->getName());
        self::assertEquals($userDto['login'], $model->getLogin());
        self::assertEquals($userDto['active'], $model->isActive());
    }

	/**
	 * @throws Exception
	 */
	public function testHydrateModel(): void
    {
        $userDto = $this->createUserDto();
        $model = new UserModel;

        $rules = [
            'id' => [],
            'creAt' => [],
            'name' => [],
            'login' => [],
            'active' => [],
            'email' => [],
        ];
        $rules = $this->normalizer->normalize($rules);

        $this->hydrator->hydrateModel($userDto, $model, $rules);

        self::assertInstanceOf(UserModel::class, $model);
        self::assertEquals($userDto['creAt'], $model->getCreAt());
        self::assertEquals($userDto['email'], $model->getEmail());
        self::assertEquals($userDto['name'], $model->getName());
        self::assertEquals($userDto['login'], $model->getLogin());
        self::assertEquals($userDto['active'], $model->isActive());
    }

    protected function createUser(): UserModel
    {
        $user = new UserModel;
        $user->setActive($this->faker->randomElement([true, false]));
        $user->setEmail($this->faker->email);
        $user->setLogin($this->faker->name);
        $user->setName($this->faker->name);

        return $user;
    }

    public function testExtract(): void
    {
        $user = $this->createUser();
        $rules = [
            'id' => [],
            'creAt' => [],
            'name' => [],
            'login' => [],
            'active' => [],
            'email' => [],
        ];
        $rules = $this->normalizer->normalize($rules);

        $dto = $this->hydrator->extract($user, $rules);

        self::assertCount(6, $dto);
        self::assertInstanceOf('DateTime', $dto['creAt']);
        self::assertEquals($user->getEmail(), $dto['email']);
        self::assertEquals($user->getName(), $dto['name']);
        self::assertEquals($user->getLogin(), $dto['login']);
        self::assertEquals($user->isActive(), $dto['active']);
    }

    public function testGetHydrator(): void
    {
        $hydrator = $this->hydrator->getHydrator();
        self::assertInstanceOf(HydratorInterface::class, $hydrator);
    }

    public function testGetExtractor(): void
    {
        $extractor = $this->hydrator->getExtractor();
        self::assertInstanceOf(ExtractorInterface::class, $extractor);
    }
}
