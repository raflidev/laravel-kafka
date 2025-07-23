namespace App\Kafka\Listener;

use Laravel\Kafka\Contracts\ConsumerMessage;

class OrderCreatedHandler
{
    public function __invoke(ConsumerMessage $message): void
    {
        logger('Menerima Kafka Message:', $message->getBody());
    }
}