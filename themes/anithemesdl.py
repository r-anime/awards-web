import argparse
import csv
import json

import requests

if __name__ == "__main__":
    def main():
        parser = argparse.ArgumentParser()
        parser.add_argument(
            "year",
            help="The year from which to get OPEDs. Run it multiple times for multiple years.",
        )
        parser.add_argument(
            "--season",
            "-s",
            help="The season from which to get OPEDs.",
            choices=["all", "winter", "spring", "summer", "fall"],
            default="all",
        )
        parser.add_argument(
            "--output",
            "-o",
            help="The output file.",
            choices=["csv", "json"],
            default="csv",
        )
        args = parser.parse_args()


        results = []
        for page in range(1, 10):  # Could use a while loop here, but range is safer
            params = {
                "filter[year]": args.year,
                "filter[season]": None if args.season == "all" else args.season,
                "filter[site]": "AniList",
                "include": "animethemes.animethemeentries.videos,animethemes.song,resources",
                "page[size]": 100,
                "page[number]": page,
            }
            r = requests.get("https://api.animethemes.moe/anime", params=params)
            result = r.json()["anime"]
            if not result:
                break
            results.extend(result)
        anime_list: list[dict] = []
        for anime in results:
            name, season = anime["name"], anime["season"]
            anilist = anime["resources"][0]["external_id"]
            for themes in anime["animethemes"]:
                oped = themes["type"]
                sequence = themes["sequence"]
                songname = themes["song"].get("title") if themes["song"] else None
                for entries in themes["animethemeentries"]:
                    version, episodes = entries["version"], entries["episodes"]
                    for videos in entries["videos"]:
                        link = videos["link"]
                        if "NCBD1080" not in link:
                            ver = ""
                            if (str(sequence) != "None"):
                                ver = str(sequence)
                            anime_list.append(
                                {
                                    "name": name,
                                    "songname": songname,
                                    "anilist": anilist,
                                    "type": str(oped) + ver,
                                    #"season": season,
                                    #"version": ,
                                    #"episodes": episodes,
                                    "link": link,
                                }
                            )
        if args.output == "csv":
            with open(
                f"animethemes_{args.year}_{args.season}.csv", "w",  encoding="utf-8", newline=""
            ) as output_file:
                dict_writer = csv.DictWriter(output_file, anime_list[0].keys())
                dict_writer.writeheader()
                dict_writer.writerows(anime_list)
        elif args.output == "json":
            with open(f"animethemes_{args.year}_{args.season}.json", "w",  encoding="utf-8") as output_file:
                json.dump(anime_list, output_file, indent=4, ensure_ascii=False)

main()