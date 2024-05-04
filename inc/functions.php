<?php
function breakline()
{
  echo "<br/><hr/><br/>";
}
function e($text)
{
  // return $text;
  echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
