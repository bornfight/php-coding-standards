# Procedure for adding new rules to the repository, after doing code review

1. Check if the error is reacurring or critical
2. Create a pull request:
    1. Write the rule in English language to correct the mistake. Use PSR wording.
    2. Write a short code snippet. [See how](https://help.github.com/articles/creating-and-highlighting-code-blocks/)
        1. Keep it generic
        2. Use minimal code required to show an example
        3. Avoid copy/pasting code from private private projects
3. Create a Pull Request
4. If the rule can be automated, write a rule for it in phpcs
