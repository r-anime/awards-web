
# 2021 /r/anime Awards Jury Allocation Algorithm

Last year, we began publishing the implementation of how we allocate jurors in the Awards in order to increase transparency. This post can be viewed [here](https://gist.github.com/JoseiToAoiTori/56bd10081e5022d51243f2e7a285dfec).

Based on feedback from Jurors and Hosts alike, we have changed the algorithm this year to be more simple and better at prioritizing applicant preferences. The first step towards this goal is to change the previous 1 - 5 score system to a preference system. This allows applicants to mark what categories they'd *most* want to be in. Not putting a category in the preference list indicates not wanting it at all. Additionally, we've bumped the number of categories a jurors can be in back up to **5**. However, a maximum of **3 categories is still maintained for the  main draft**, meaning additional categories will only be assigned if jurors are needed to fill up the category.

## The Extreme TL;DR
We've maintained the threshold based allocation system introduced last year, but have created a simpler preference system in lieu of RNG pruning. This system aims maintain or even surpass the diversity achieved in last year's jury, while adding some more incentive to aim for a higher score on the application as well as some more clarity to the allocation process.

Preliminary tests with retrofitted data from last year shows that categories achieve a lower average preference ranking (meaning more jurors get categories out of their top preferred categories) as well as an increase of around 15 or so total jurors in the awards that we passed up last year.

## Grading
Hosts will grade the applicant's answer based on the following scale:

**0:** A failed answer. An answer with this grade cannot get into the category the grade corresponds to under any circumstances. Reserved for complete non-answers or utter incoherence. Examples are blank "aaaaaaaaa" spams or something that doesn't even remotely resemble a point.

**1:** A backup answer. An answer with this grade will likely not get into the category *except* if the category is missing jurors and no one else wants the category. These answers technically coherent and maybe have an opinion ("I like this show it is good because it's good") but fail to do any real explanation or analysis.

**2:** An acceptable answer. It is good enough to be in the awards in general. Answers for this grade will very likely get into this category unless it is popular. These answers are generally fine, answer the question, and raise points with explanations, but don't do any deeper analysis.

**3:** A good answer. An answer with this grade is almost guaranteed to get into its corresponding category if the applicant has it at a high priority. Exceptions naturally occur with mega popular categories like Anime of the Year. These answers include proper analysis, argue their points well, and generally answer the question fully and satisfactory, but don't say anything extraordinary or above and beyond.

**4:** An exceptional answer. A stronger guarantee to get into the corresponding category than a 3, and an almost 100% certainty if it's an applicants' #1 priority. These answers that go above and beyond what is needed and practically do everything perfectly.

Each answer is graded by 3 Hosts and the average determines the Juror's actual grade. This then translates into how a Juror is allocated by the algorithm.

## The Draft

For each applicant, then, we have the following information: Their category score, their category preference, the maximum amount of categories they want to be in, and whether or not they are willing to fill. The algorithm then does the following:

* Step 1: Pick a category. For this category, produce a list of jurors that

  * A) Have this category as their #1 preference.

  * B) Have a score in that category (e.g. their Genre score for Action) above 2.5, corresponding to at minimum two 3s and one 2 from three Hosts.

  * C) Has not yet reached their maximum amount of categories (up to 3).

* Step 2: Sort the list by score (higher scores first) and **immunity points** (more on them in the next step).

* Step 3: Put all the Jurors in the list into the category one by one, stopping if the category is full. Any juror that *didn't* get in is awarded an **immunity point**, ensuring they are prioritized over other people with the same score later on.

* Step 4: Repeat steps 1 to 3 for every category.

* Step 5: Repeat steps 1 to 4 for the next preference, e.g. #2, then #3, then #4.

This constitutes the **first main draft**. It insures that those that have a category at their highest preference and whose answer was deemed good get into the category. If more people want the category equally, score is the determining factor. If the preference and score is the same, immunity points is the determining factor. As such, the algorithm allocates jurors in such a way that should satisfy as many people as possible. By design, the draft continues until no more Jurors can be allocated.

After Steps 1 to 5, the **second main draft** occurs. This simply repeats Steps 1 to 5 except the qualifying score is now 1.5, corresponding to at minimum two 2s and one 1 from three Hosts. The effect is that people that score a 2 are allocated *after* those that score a 3 and 4. They will still be considering by their highest preference and will be allocated to the categories they want provided that category is not full.

After the first and second main drafts, we look to categories with less than 7 jurors. We then begin the **fill draft**:

* Step 1: Pick the category with the lowest number of Jurors (tiebreaks are resolved by which category has the least available jurors, then randomly if the number is equal). For this category, produce a list of jurors that

  * A) Have this category in their preference list.

  * B) Have a score in that category (e.g. their Genre score for Action) above 1.5, corresponding to at minimum two 2s and one 1 from three Hosts.

  * C) Have not gotten into another category in the fill draft.

* Step 2: Sort the list by preference and then by score (higher scores first).

* Step 3: Put the jurors into the category one at a time until the category has more jurors than another category.

* Step 4: Repeat steps 1 to 3 until every category has reached FIGURE THIS OUT.

This makes sure that the category with the lowest jurors are sorted first and that those that prefer it more get into it first. Note that due to the design of the main drafts, any juror that has any category eligible for the fill draft in their preferences has already reached their maximum number of categories. As such, those that check the willing to fill box can at most end up with (maximum + 1) categories. In the final step, if there are still categories that are not yet filled, we repeat the fill draft except with a qualifying score of 1 (we want to make sure that every host thinks the answer is good enough for a 1). This is the **backup fill draft**.

And that's it! If you want to know more about the nitty gritty details you can read the commented code [here](https://github.com/r-anime/awards-web/blob/master/util/allocations.js).
