<?php

namespace Plastonick\Euros;

class MessengerCollection implements Messenging
{
    /**
     * @var Messenger[]
     */
    private array $messengers = [];

    public function register(Messenger $messager): self
    {
        $this->messengers[$messager->config->getWebHookUrl()] = $messager;

        return $this;
    }

    public function deregister(string $webhookUrl): self
    {
        unset($this->messengers[$webhookUrl]);

        return $this;
    }

    public function matchStarting(Game $match): void
    {
        foreach ($this->messengers as $messager) {
            $messager->matchStarting($match);
        }
    }

    public function matchComplete(Game $match): void
    {
        foreach ($this->messengers as $messager) {
            $messager->matchComplete($match);
        }
    }

    public function goalScored(Team $scoringTeam, Game $match): void
    {
        foreach ($this->messengers as $messager) {
            $messager->goalScored($scoringTeam, $match);
        }
    }

    public function goalDisallowed(Game $match): void
    {
        foreach ($this->messengers as $messager) {
            $messager->goalDisallowed($match);
        }
    }
}
