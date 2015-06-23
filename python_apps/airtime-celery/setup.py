from setuptools import setup
from subprocess import call
import os
import sys

install_args = ['install', 'install_data', 'develop']
run_postinst = False

# XXX Definitely not the best way of doing this...
if sys.argv[1] in install_args and "--no-init-script" not in sys.argv:
    run_postinst = True
    data_files = [('/etc/default', ['install/conf/airtime-celery']),
                  ('/etc/init.d', ['install/initd/airtime-celery'])]
else:
    if "--no-init-script" in sys.argv:
        run_postinst = True  # We still want to run the postinst here
        sys.argv.remove("--no-init-script")
    data_files = []


def postinst():
    # Make /etc/init.d file executable and set proper
    # permissions for the defaults config file
    os.chmod('/etc/init.d/airtime-celery', 0755)
    os.chmod('/etc/default/airtime-celery', 0640)
    # Make the airtime log directory group-writable
    os.chmod('/var/log/airtime', 0775)

    # Create the Celery user
    call(['adduser', '--no-create-home', '--home', '/var/lib/celery', '--gecos', '""', '--disabled-login', 'celery'])
    # Add celery to the www-data group
    call(['usermod', '-G', 'www-data', '-a', 'celery'])

    print "Reloading initctl configuration"
    call(['initctl', 'reload-configuration'])
    print "Setting Celery to start on boot"
    call(['update-rc.d', 'airtime-celery', 'defaults'])
    print "Run \"sudo service airtime-celery restart\" now."

setup(name='airtime-celery',
      version='0.1',
      description='Airtime Celery service',
      url='http://github.com/sourcefabric/Airtime',
      author='Sourcefabric',
      author_email='duncan.sommerville@sourcefabric.org',
      license='MIT',
      packages=['airtime-celery'],
      install_requires=[
          'soundcloud',
          'celery',
          'kombu',
          'configobj'
      ],
      zip_safe=False,
      data_files=data_files)

if run_postinst:
    postinst()
