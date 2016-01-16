guard :shell do
  PHPUNIT = 'phpunit --bootstrap tests/bootstrap.php'

  watch(/^tests\/.*Test\.php/) { |match|
    system("#{PHPUNIT} #{match[0]}")
  }

  watch(/^src\/(.*).php$/) { |match|
    system("#{PHPUNIT} tests/#{match[1]}Test.php")
  }

  PHPCS = 'phpcs --standard=phpcs.xml '

  watch(/^(src|tests)\/(.*).php$/) { |match|
    system("#{PHPCS} #{match[1]}/#{match[2]}.php")
  }
end
