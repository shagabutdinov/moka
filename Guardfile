guard :shell do
  COMMAND = 'phpunit --bootstrap tests/bootstrap.php'

  watch(/^tests\/.*Test\.php/) { |match|
    system("#{COMMAND} #{match[0]}")
  }

  watch(/^src\/(.*).php$/) { |match|
    system("#{COMMAND} tests/#{match[1]}Test.php")
  }
end
