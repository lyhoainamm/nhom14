@echo off
@rem Licensed to the Apache Software Foundation (ASF) under one or more
@rem contributor license agreements.  See the NOTICE file distributed with
@rem this work for additional information regarding copyright ownership.
@rem The ASF licenses this file to You under the Apache License, Version 2.0
@rem (the "License"); you may not use this file except in compliance with
@rem the License.  You may obtain a copy of the License at
@rem
@rem    http://www.apache.org/licenses/LICENSE-2.0
@rem
@rem Unless required by applicable law or agreed to in writing, software
@rem distributed under the License is distributed on an "AS IS" BASIS,
@rem WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
@rem See the License for the specific language governing permissions and
@rem limitations under the License.

@rem Convenient way to invoke a gradle command with all Scala versions supported
@rem by default
@rem This script was originally designed to support multiple Scala versions (2.12 and 2.13),
@rem but as Scala 2.12 is no longer supported, this script is no longer necessary.
@rem We are keeping it for backwards compatibility. It will be removed in a future release.
echo Warning: This script is deprecated and will be removed in a future release.
if exist "%~dp0gradlew.bat" (
  call "%~dp0gradlew.bat" %* -PscalaVersion=2.13
) else (
  echo gradlew.bat not found, falling back to system Gradle...
  gradle %* -PscalaVersion=2.13
)

