import subprocess
from analyzer import Analyzer


class ReplayGainAnalyzer(Analyzer):
    ''' This class extracts the cue-in time, cue-out time, and length of a track using silan. '''

    BG1770GAIN_EXECUTABLE = 'bg1770gain'

    def __init__(self):
        pass

    @staticmethod
    def analyze(filename, metadata):
        ''' Extracts the Replaygain loudness normalization factor of a track.
        :param filename: The full path to the file to analyzer
        :param metadata: A metadata dictionary where the results will be put
        :return: The metadata dictionary
        '''
        ''' The -d 00:01:00 flag means it will let the decoding run for a maximum of 1 minute. This is a safeguard
            in case the libavcodec decoder gets stuck in an infinite loop.
        '''
        command = [ReplayGainAnalyzer.BG1770GAIN_EXECUTABLE, '--replaygain', '-d', '00:01:00', '-f', 'JSON', filename]
        try:
            results_json = subprocess.check_output(command)
            silan_results = json.loads(results_json)
            metadata['length_seconds'] = float(silan_results['file duration'])
            # Conver the length into a formatted time string
            track_length = datetime.timedelta(seconds=metadata['length_seconds'])
            metadata["length"] = str(track_length)
            metadata['cuein'] = silan_results['sound'][0][0]
            metadata['cueout'] = silan_results['sound'][0][1]

        except OSError as e: # silan was not found
            logging.warn("Failed to run: %s - %s. %s" % (command[0], e.strerror, "Do you have silan installed?"))
        except subprocess.CalledProcessError as e: # silan returned an error code
            logging.warn("%s %s %s", e.cmd, e.message, e.returncode)
        except Exception as e:
            logging.warn(e)

        return metadata