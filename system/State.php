<?php

namespace System;

abstract class State {
	public float $microtimeInit = -1;
	public float $executionTimeSeconds = -1;
	public string|null $responseContent = null;
}
