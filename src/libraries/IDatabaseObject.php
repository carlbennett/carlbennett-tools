<?php

namespace CarlBennett\Tools\Libraries;

interface IDatabaseObject {

  function allocate();
  function allocateMany();
  function commit();

}
