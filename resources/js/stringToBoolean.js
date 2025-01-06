export default function stringToBoolean(string)
{
    if(/^(true|1|on)$/i.test(string)) {
        return true;
    }
    if(/^(false|0|off)$/i.test(string) || string == '') {
        return false;
    }
    throw new Error('Input value must be true or false!');
}
