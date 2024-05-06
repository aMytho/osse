use std::env;

/**
 * Tries to get the config path from the args
 * If no arg, the default path is returned
 */
pub fn get_config_path() -> String {
    let args: Vec<String> = env::args().collect();

    if args.len() > 1 {
        return args[1].clone();
    } else {
        return String::from("config.json");
    }
}