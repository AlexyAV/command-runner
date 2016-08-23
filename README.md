[![Build Status](https://travis-ci.org/AlexyAV/command-runner.svg?branch=master)](https://travis-ci.org/AlexyAV/command-runner)
# Command runner
Execute console command and process result.

This library allows you to launch an external application via the console and process the results.
Also can be use to safely run daemons.

##Installation

Add to your composer.json 
```
"command-runner/command-runner": "dev-master"
```

Example of single command:

```php
$commandRunner = new CommandRunner;
$result = $commandRunner->setCommand('pwd')->execute();

//  [
//      'output'     => 'path_to_your_working_dir',
//      'resultCode' => 0
//  ]
```

With additionals arguments:

```php
...
$result = $commandRunner->setCommand('ls -la')
                        ->setArgument(['./assets'])
                        ->execute();
                        
//  [
//      'output'     => [
//          'drwxrwxr-x 1 vagrant vagrant  4096 Aug 22 13:32 e45d6ac2',
//          'drwxrwxr-x 1 vagrant vagrant  4096 May 12 17:51 efd003fa',
//          ...
//      ],
//      'resultCode' => 0
//  ]
```
Arguments are optional. You can path command with all arguments to `setCommand` method.

If you want to get raw command output:

```php
...
$commandRunner->setRawOutput(true); // false by default
                        
//  [
//      'output'     => 
//          'drwxrwxr-x 1 vagrant vagrant  4096 Aug 22 13:32 e45d6ac2
//          drwxrwxr-x 1 vagrant vagrant  4096 May 12 17:51 efd003fa
//          ...
//      ,
//      'resultCode' => 0
//  ]
```
Accordingly to redirect standard command output use `setWaitForOutput(false)`.

## Command queue

Class `CommandQueue` can be used to perform multiple commands with the possibility of setting the individual parameters:

```php
$commandQueue = new CommandQueue();
$commandQueue->setCommandQueue(
    [
        // first command
        [
            'command'   => 'ps aux | grep',
            'escape'    => false,           // false by default
            'arguments' => ['rabbitmq'],    // optional
            'options'   => [
                'saveOutputName' => 'ps_output_file_name'
            ],
        ],
        // second command
        [
            'command' => 'ls -la',
            'escape'  => false,
            'options' => [
                'saveOutputName' => 'ls_output_file_name'
            ],
        ],
         // call daemon
        [
            'command' => './start_daemon',
            'escape'  => false,
            'options' => [
                'waitForOutput' => false
            ],
        ]
    ]
);

$result = $commandQueue->execute();

// Source array will be modified with command results:
// [
//     'command'     => 'ls -la',
//     'escape'      => false,
//     'options'     => [
//         'saveOutputName' => 'ls_output_file_name'
//     ],
//     'output'      => [
//           ...
//           'drwxrwxr-x 1 vagrant vagrant  4096 Aug 22 13:30 fonts',
//           'drwxrwxr-x 1 vagrant vagrant  4096 Aug 22 13:30 img'
//           ...
//     'resultCode' => 0
// ]
```

## Save output

Every command result can be saved to file. By default all files saves to `/tmp/commandRunnerOutput`. Create new instance of `CommandOutput` class to change default behavior:

```php
...
$commandOutput = new CommandOutput;
$commandOutput->setOutputPath(__DIR__);

$commandRunner->setCommandOutput($commandOutput)
              ->setSaveOutputName('resultFileName.txt');
...
// File with command result will be saved to working dir.
```
You can implement `OutputInterface` for you custom output handler.

In case of command queue output handler can be specified with:

```php
...
$commandOutput = new CommandOutput;
$commandOutput->setOutputPath(__DIR__);

$commandQueue->setCommandQueue(
    [
        [
            'command'   => 'ps aux | grep',
            'escape'    => false,           // false by default
            'arguments' => ['rabbitmq'],    // optional
            'options'   => [
                'saveOutputName'   => 'ps_output_file_name',
                'setCommandOutput' => $commandOutput
            ],
        ],
    ]
);
...
```
