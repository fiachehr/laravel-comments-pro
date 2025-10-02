<?php
namespace Fiachehr\Comments\Events;
use Fiachehr\Comments\Models\Reaction;
class ReactionToggled { public function __construct(public Reaction $reaction) {} }
