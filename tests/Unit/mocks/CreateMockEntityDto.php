<?php

namespace Unit\mocks;

use stdClass;

class CreateMockEntityDto extends stdClass
{
  public string $name = '';
  public string $description = '';
  public MockColorType $colorType = MockColorType::RED;
}